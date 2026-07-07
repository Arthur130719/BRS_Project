<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\IsolirLog;
use App\Models\Notifikasi;
use App\Models\Pelanggan;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * AutoIsolirCommand — Perintah Artisan untuk Isolir Otomatis
 *
 * Dijalankan oleh scheduler setiap hari pada jam yang dikonfigurasi.
 * Fitur:
 * - Cek pelanggan dengan invoice overdue melewati grace period → isolir
 * - Kirim warning 3 hari sebelum isolir (prinsip fairness / Isu Sosial)
 * - Log semua aksi ke activity_logs dan isolir_logs (audit trail)
 */
class AutoIsolirCommand extends Command
{
    protected $signature   = 'netcore:auto-isolir';
    protected $description = 'Otomatis isolir pelanggan yang tagihan jatuh tempo melewati grace period';

    public function handle(): int
    {
        $isAktif     = SystemSetting::get('auto_isolir_enabled', true);
        $gracePeriod = (int) SystemSetting::get('grace_period_days', 0);

        if (!$isAktif) {
            $this->info('Auto-isolir dinonaktifkan. Dilewati.');
            return self::SUCCESS;
        }

        $this->info('=== NetCORE Auto-Isolir ===');
        $this->info('Grace period: ' . $gracePeriod . ' hari');
        $this->newLine();

        // ── STEP 1: Kirim WARNING 3 hari sebelum isolir ─────────────────────
        $warningDate = Carbon::today()->addDays($gracePeriod > 3 ? 3 : 0);
        $batasWarning = Carbon::today()->subDays(max(0, $gracePeriod - 3));

        if ($gracePeriod >= 3) {
            $candidateWarning = Pelanggan::where('status', 'active')
                ->whereHas('invoices', fn($q) => $q
                    ->where('status', 'unpaid')
                    ->whereDate('tgl_jatuh_tempo', $batasWarning)
                )
                ->with('invoices:id,pelanggan_id,no_invoice,tgl_jatuh_tempo')
                ->get();

            foreach ($candidateWarning as $p) {
                $invoice = $p->invoices
                    ->where('status', 'unpaid')
                    ->first();

                // Cek apakah warning sudah pernah dikirim hari ini
                $alreadyWarned = Notifikasi::where('type', 'warning')
                    ->where('title', 'like', 'Peringatan Isolir: ' . $p->nama)
                    ->whereDate('created_at', today())
                    ->exists();

                if (!$alreadyWarned) {
                    Notifikasi::create([
                        'type'      => 'warning',
                        'title'     => 'Peringatan Isolir: ' . $p->nama,
                        'deskripsi' => 'Pelanggan ' . $p->username_pppoe .
                                       ' akan diisolir dalam 3 hari jika tagihan ' .
                                       ($invoice?->no_invoice ?? '-') . ' belum dibayar.',
                    ]);
                    $this->line("  ⚠ Warning: {$p->nama} ({$p->username_pppoe})");
                }
            }
        }

        $this->newLine();

        // ── STEP 2: Isolir pelanggan yang melewati grace period ──────────────
        $batasIsolir = Carbon::today()->subDays($gracePeriod);

        $pelanggans = Pelanggan::where('status', 'active')
            ->whereHas('invoices', fn($q) => $q
                ->where('status', 'unpaid')
                ->where('tgl_jatuh_tempo', '<', $batasIsolir)
            )
            ->with(['invoices' => fn($q) => $q
                ->where('status', 'unpaid')
                ->where('tgl_jatuh_tempo', '<', $batasIsolir)
                ->orderBy('tgl_jatuh_tempo')
            ])
            ->get();

        $count = 0;
        foreach ($pelanggans as $pelanggan) {
            $invoice = $pelanggan->invoices->first();

            $pelanggan->update([
                'status'    => 'suspend',
                'isolir_by' => 'auto',
                'isolir_at' => now(),
            ]);

            IsolirLog::create([
                'pelanggan_id' => $pelanggan->id,
                'invoice_id'   => $invoice?->id,
                'aksi'         => 'isolir',
                'metode'       => 'auto',
                'user_id'      => null,
                'alasan'       => 'Tagihan jatuh tempo melewati grace period ' . $gracePeriod . ' hari',
            ]);

            Notifikasi::create([
                'type'      => 'danger',
                'title'     => 'Auto-Isolir: ' . $pelanggan->nama,
                'deskripsi' => 'Pelanggan ' . $pelanggan->username_pppoe .
                               ' diisolir otomatis. Invoice: ' . ($invoice?->no_invoice ?? '-'),
            ]);

            // Catat ke activity log (audit trail)
            ActivityLog::create([
                'user_id'     => null,
                'user_name'   => 'System (Auto-Isolir)',
                'user_role'   => 'system',
                'action'      => 'suspend',
                'module'      => 'Pelanggan',
                'description' => 'Auto-isolir: ' . $pelanggan->nama . ' (' . $pelanggan->username_pppoe . ')',
                'model_type'  => Pelanggan::class,
                'model_id'    => $pelanggan->id,
                'ip_address'  => '127.0.0.1',
                'method'      => 'SCHEDULE',
            ]);

            $count++;
            $this->line("  ✓ Isolir: {$pelanggan->nama} ({$pelanggan->username_pppoe})");
        }

        // ── STEP 3: Update metadata ──────────────────────────────────────────
        SystemSetting::set('auto_isolir_last_run',   now()->toDateTimeString());
        SystemSetting::set('auto_isolir_last_count', $count);

        $this->newLine();
        $this->info("Auto-isolir selesai. Total diisolir: {$count} pelanggan.");

        return self::SUCCESS;
    }
}
