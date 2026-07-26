<?php

namespace App\Http\Controllers;

use App\Models\IsolirLog;
use App\Models\Nas;
use App\Models\Notifikasi;
use App\Models\Olt;
use App\Models\Paket;
use App\Models\Pelanggan;
use Illuminate\Http\Request;

use App\Services\MikrotikService;

class PelangganController extends Controller
{
    protected $mikrotikService;

    public function __construct(MikrotikService $mikrotikService)
    {
        $this->mikrotikService = $mikrotikService;
    }

    public function index(Request $request)
    {
        $query = Pelanggan::with(['paket', 'nas', 'olt']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('nama', 'like', "$s%")
                ->orWhere('nama', 'like', "% $s%")
                ->orWhere('username_pppoe', 'like', "$s%")
                ->orWhere('username_pppoe', 'like', "% $s%")
                ->orWhere('phone', 'like', "%$s%")
                ->orWhere('ip_address', 'like', "%$s%")
            );
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('paket_id')) {
            $query->where('paket_id', $request->paket_id);
        }

        $pelanggans = $query->latest()->paginate(15)->withQueryString();
        $pakets     = Paket::where('is_active', true)->get();

        return view('pelanggan.index', compact('pelanggans', 'pakets'));
    }

    public function create()
    {
        if (!auth()->user()->hasRole(['admin', 'kasir'])) {
            abort(403, 'Akses ditolak.');
        }

        $pakets = Paket::where('is_active', true)->get();
        $nasList = Nas::where('status', 'online')->get();
        $oltList = Olt::where('status', 'online')->get();

        return view('pelanggan.create', compact('pakets', 'nasList', 'oltList'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasRole(['admin', 'kasir'])) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'username_pppoe' => 'required|unique:pelanggans,username_pppoe|max:100',
            'password_pppoe' => 'required|min:3',
            'nama'           => 'required|max:200',
            'phone'          => 'nullable|max:20',
            'phone_2'        => 'nullable|max:20',
            'alamat'         => 'nullable|max:500',
            'latitude'       => 'nullable|numeric|between:-90,90',
            'longitude'      => 'nullable|numeric|between:-180,180',
            'paket_id'       => 'required|exists:pakets,id',
            'nas_id'         => 'nullable|exists:nas,id',
            'olt_id'         => 'nullable|exists:olts,id',
            'ip_pool'        => 'nullable|max:50',
        ]);

        $validated['tgl_aktif'] = now()->format('Y-m-d');
        $validated['expiry'] = now()->addMonth()->format('Y-m-d');

        $pelanggan = Pelanggan::create($validated);
        
        // Buat tagihan pertama secara otomatis (PSB)
        if ($pelanggan->paket) {
            \App\Models\Invoice::create([
                'pelanggan_id'    => $pelanggan->id,
                'no_invoice'      => \App\Models\Invoice::generateNoInvoice(),
                'periode'         => \Carbon\Carbon::now()->translatedFormat('F Y'),
                'nominal'         => $pelanggan->paket->harga,
                'tgl_jatuh_tempo' => now()->addDays(7)->format('Y-m-d'),
                'keterangan'      => 'Tagihan Pemasangan Baru (Bulan Pertama)',
                'status'          => 'unpaid',
            ]);
        }

        Notifikasi::create([
            'type'      => 'info',
            'title'     => 'Pelanggan Baru: ' . $pelanggan->nama,
            'deskripsi' => 'PPPoE: ' . $pelanggan->username_pppoe . ' — Paket: ' . $pelanggan->paket?->nama,
        ]);

        // ==========================================
        // MIKROTIK INTEGRATION: Create PPPoE Secret
        // ==========================================
        if ($pelanggan->nas) {
            $profileName = $pelanggan->paket ? ($pelanggan->paket->mikrotik_profile ?: $pelanggan->paket->nama) : 'default';
            $success = $this->mikrotikService->addPppoeUser(
                $pelanggan->nas, 
                $pelanggan->username_pppoe, 
                $pelanggan->password_pppoe, 
                $profileName
            );

            if (!$success) {
                return redirect()->route('pelanggan.index')->with('warning', 'Pelanggan berhasil disimpan ke database web, namun GAGAL ditambahkan ke MikroTik. Pastikan router menyala dan API terhubung.');
            }
        }

        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil ditambahkan ke web dan MikroTik.');
    }

    public function show(Pelanggan $pelanggan)
    {
        $pelanggan->load(['paket', 'nas', 'olt', 'onu', 'invoices' => fn($q) => $q->latest()->take(10), 'isolirLogs' => fn($q) => $q->with('user')->latest()->take(10)]);
        return view('pelanggan.show', compact('pelanggan'));
    }

    public function edit(Pelanggan $pelanggan)
    {
        if (!auth()->user()->hasRole(['admin','kasir'])) {
            abort(403, 'Akses ditolak.');
        }

        $pakets  = Paket::where('is_active', true)->get();
        $nasList = Nas::where('status', 'online')->get();
        $oltList = Olt::where('status', 'online')->get();

        return view('pelanggan.edit', compact('pelanggan', 'pakets', 'nasList', 'oltList'));
    }

    public function update(Request $request, Pelanggan $pelanggan)
    {
        if (!auth()->user()->hasRole(['admin','kasir'])) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'username_pppoe' => 'required|unique:pelanggans,username_pppoe,' . $pelanggan->id . '|max:100',
            'password_pppoe' => 'nullable|min:3',
            'nama'           => 'required|max:200',
            'phone'          => 'nullable|max:20',
            'phone_2'        => 'nullable|max:20',
            'alamat'         => 'nullable|max:500',
            'latitude'       => 'nullable|numeric|between:-90,90',
            'longitude'      => 'nullable|numeric|between:-180,180',
            'paket_id'       => 'required|exists:pakets,id',
            'nas_id'         => 'nullable|exists:nas,id',
            'olt_id'         => 'nullable|exists:olts,id',
            'ip_pool'        => 'nullable|max:50',
            'tgl_aktif'      => 'nullable|date',
            'expiry'         => 'nullable|date',
        ]);

        if (empty($validated['password_pppoe'])) {
            unset($validated['password_pppoe']);
        } else {
            // Revoke all existing tokens if password is changed
            $pelanggan->tokens()->delete();
        }

        $oldUsername = $pelanggan->username_pppoe;
        $pelanggan->update($validated);
        $pelanggan->refresh();

        // ==========================================
        // MIKROTIK INTEGRATION: Update PPPoE Secret
        // ==========================================
        if ($pelanggan->nas) {
            $profileName = $pelanggan->paket ? ($pelanggan->paket->mikrotik_profile ?: $pelanggan->paket->nama) : 'default';
            
            // If they didn't provide a new password in the form, don't pass it to mikrotik (keep old)
            // But if we want to sync, we might need the actual password. Let's pass the updated raw password if provided.
            $password = $request->password_pppoe ?: null;
            
            $success = $this->mikrotikService->updatePppoeUser(
                $pelanggan->nas,
                $oldUsername,
                $pelanggan->username_pppoe,
                $password,
                $profileName
            );

            if (!$success) {
                return redirect()->route('pelanggan.index')->with('warning', 'Data pelanggan berhasil diperbarui di web, namun GAGAL disinkronisasi ke MikroTik.');
            }
        }

        return redirect()->route('pelanggan.index')->with('success', 'Data pelanggan berhasil diperbarui di web dan MikroTik.');
    }

    public function destroy(Pelanggan $pelanggan)
    {
        if (!auth()->user()->hasRole(['admin', 'kasir'])) {
            abort(403, 'Akses ditolak.');
        }

        // ==========================================
        // MIKROTIK INTEGRATION: Remove PPPoE Secret
        // ==========================================
        $nas = $pelanggan->nas;
        $username = $pelanggan->username_pppoe;
        
        $pelanggan->delete();

        if ($nas) {
            $success = $this->mikrotikService->removePppoeUser($nas, $username);
            
            if (!$success) {
                return redirect()->route('pelanggan.index')->with('warning', 'Pelanggan berhasil dihapus dari web, tapi GAGAL dihapus dari MikroTik. Silakan hapus manual di Winbox.');
            }
        }

        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil dihapus dari web dan MikroTik.');
    }

    public function suspend(int $id)
    {
        $pelanggan = Pelanggan::findOrFail($id);

        $pelanggan->update([
            'status'    => 'suspend',
            'isolir_by' => 'manual:' . auth()->id(),
            'isolir_at' => now(),
        ]);

        if ($pelanggan->nas) {
            $this->mikrotikService->changePppoeProfile($pelanggan->nas, $pelanggan->username_pppoe, 'isolir');
        }

        $hasUnpaid = \App\Models\Invoice::where('pelanggan_id', $pelanggan->id)
            ->where('status', 'unpaid')
            ->exists();

        if (!$hasUnpaid && $pelanggan->paket) {
            \App\Models\Invoice::create([
                'pelanggan_id'    => $pelanggan->id,
                'no_invoice'      => \App\Models\Invoice::generateNoInvoice(),
                'periode'         => \Carbon\Carbon::now()->translatedFormat('F Y'),
                'nominal'         => $pelanggan->paket->harga,
                'tgl_jatuh_tempo' => now()->addDays(7)->format('Y-m-d'),
                'keterangan'      => 'Tagihan otomatis (Suspend Manual)',
                'status'          => 'unpaid',
            ]);
        }

        IsolirLog::create([
            'pelanggan_id' => $pelanggan->id,
            'aksi'         => 'isolir',
            'metode'       => 'manual',
            'user_id'      => auth()->id(),
            'alasan'       => 'Isolir manual oleh ' . auth()->user()->name,
        ]);

        return back()->with('success', 'Pelanggan ' . $pelanggan->nama . ' berhasil diisolir.');
    }

    public function aktifkan(int $id)
    {
        $pelanggan = Pelanggan::findOrFail($id);

        $pelanggan->update([
            'status'    => 'active',
            'isolir_by' => null,
            'isolir_at' => null,
        ]);

        if ($pelanggan->nas && $pelanggan->paket) {
            $profileName = $pelanggan->paket->mikrotik_profile ?: $pelanggan->paket->nama;
            $this->mikrotikService->changePppoeProfile($pelanggan->nas, $pelanggan->username_pppoe, $profileName);
        }

        IsolirLog::create([
            'pelanggan_id' => $pelanggan->id,
            'aksi'         => 'aktifkan',
            'metode'       => 'manual',
            'user_id'      => auth()->id(),
            'alasan'       => 'Diaktifkan manual oleh ' . auth()->user()->name,
        ]);

        return back()->with('success', 'Pelanggan ' . $pelanggan->nama . ' berhasil diaktifkan.');
    }
    public function liveSession(Pelanggan $pelanggan)
    {
        $cachedSessions = \Illuminate\Support\Facades\Cache::get('mikrotik_live_sessions', []);
        
        foreach ($cachedSessions as $session) {
            if (isset($session['name']) && $session['name'] === $pelanggan->username_pppoe) {
                return response()->json([
                    'status' => 'online',
                    'ip_address' => $session['address'] ?? '-',
                    'uptime' => $session['uptime'] ?? '-',
                    'download' => isset($session['tx-byte']) ? $this->formatBytes($session['tx-byte']) : '0 B',
                    'upload' => isset($session['rx-byte']) ? $this->formatBytes($session['rx-byte']) : '0 B',
                    'rate' => isset($session['tx-rate']) && isset($session['rx-rate']) ? 
                              $this->formatRate($session['tx-rate']) . ' / ' . $this->formatRate($session['rx-rate']) : '-',
                ]);
            }
        }

        return response()->json([
            'status' => 'offline',
            'ip_address' => '-',
            'uptime' => '-',
            'download' => '0 B',
            'upload' => '0 B',
            'rate' => '0 bps / 0 bps'
        ]);
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $bytes = (float) $bytes;
        if ($bytes <= 0) return '0 B';
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    private function formatRate($bps, $precision = 1)
    {
        $bps = (float) $bps;
        if ($bps <= 0) return '0 bps';
        $units = array('bps', 'Kbps', 'Mbps', 'Gbps');
        $pow = floor(($bps ? log($bps) : 0) / log(1000));
        $pow = min($pow, count($units) - 1);
        $bps /= pow(1000, $pow);
        return round($bps, $precision) . ' ' . $units[$pow];
    }

    public function importRsc(Request $request)
    {
        $request->validate([
            'nas_id' => 'required|exists:nas,id',
            'rsc_file' => 'required|file|mimes:txt,rsc|max:2048',
        ]);

        $file = $request->file('rsc_file');
        $content = file_get_contents($file->getRealPath());

        // Normalize line continuations (backslash followed by newline)
        $content = preg_replace('/\\\\\r?\n\s*/', '', $content);
        $lines = explode("\n", $content);

        // Map existing paket (lowercase for matching)
        $pakets = \App\Models\Paket::all()->keyBy(function ($paket) {
            return strtolower($paket->mikrotik_profile ?: $paket->nama);
        });

        // Get existing usernames to avoid duplicates
        $existingUsernames = \App\Models\Pelanggan::pluck('username_pppoe')->map(function ($u) {
            return strtolower($u);
        })->toArray();

        $imported = 0;
        $skipped = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            
            // Look for PPP Secret add commands
            if (strpos($line, 'add ') === 0 && strpos($line, 'service=pppoe') !== false) {
                $name = null;
                $password = null;
                $profile = null;

                if (preg_match('/name="?([^"\s]+)"?/', $line, $matches)) {
                    $name = $matches[1];
                }
                if (preg_match('/password="?([^"\s]+)"?/', $line, $matches)) {
                    $password = $matches[1];
                }
                if (preg_match('/profile="?([^"\s]+)"?/', $line, $matches)) {
                    $profile = $matches[1];
                }

                if ($name && $password) {
                    if (in_array(strtolower($name), $existingUsernames)) {
                        $skipped++;
                        continue;
                    }

                    // Find paket id
                    $paket_id = null;
                    if ($profile && isset($pakets[strtolower($profile)])) {
                        $paket_id = $pakets[strtolower($profile)]->id;
                    }

                    \App\Models\Pelanggan::create([
                        'username_pppoe' => $name,
                        'password_pppoe' => $password,
                        'nama'           => ucwords(str_replace(['.', '_', '-'], ' ', $name)),
                        'nas_id'         => $request->nas_id,
                        'paket_id'       => $paket_id,
                        'status'         => 'active',
                        'tgl_aktif'      => now(),
                    ]);

                    $existingUsernames[] = strtolower($name);
                    $imported++;
                }
            }
        }

        return back()->with('success', "Berhasil import {$imported} rahasia PPPoE. ({$skipped} dilewati karena username ganda).");
    }
}
