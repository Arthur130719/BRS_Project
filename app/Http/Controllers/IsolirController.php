<?php

namespace App\Http\Controllers;

use App\Models\IsolirLog;
use App\Models\Notifikasi;
use App\Models\Pelanggan;
use App\Services\MikrotikService;
use Illuminate\Http\Request;

class IsolirController extends Controller
{
    protected $mikrotikService;

    public function __construct(MikrotikService $mikrotikService)
    {
        $this->mikrotikService = $mikrotikService;
    }

    public function isolir(Pelanggan $pelanggan, Request $request)
    {
        $validated = $request->validate([
            'alasan' => 'nullable|max:255',
        ]);

        $pelanggan->update([
            'status'    => 'suspend',
            'isolir_by' => 'manual:' . auth()->id(),
            'isolir_at' => now(),
        ]);

        // Change to Isolir profile in MikroTik if NAS is set
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
                'keterangan'      => 'Tagihan otomatis (Isolir Manual)',
                'status'          => 'unpaid',
            ]);
        }

        IsolirLog::create([
            'pelanggan_id' => $pelanggan->id,
            'aksi'         => 'isolir',
            'metode'       => 'manual',
            'user_id'      => auth()->id(),
            'alasan'       => $validated['alasan'] ?? 'Isolir manual oleh ' . auth()->user()->name,
        ]);

        Notifikasi::create([
            'type'      => 'warning',
            'title'     => 'Isolir Manual: ' . $pelanggan->nama,
            'deskripsi' => 'Dilakukan oleh: ' . auth()->user()->name,
        ]);

        return back()->with('success', 'Pelanggan ' . $pelanggan->nama . ' berhasil diisolir.');
    }

    public function aktifkan(Pelanggan $pelanggan, Request $request)
    {
        $validated = $request->validate([
            'alasan' => 'nullable|max:255',
        ]);

        $pelanggan->update([
            'status'    => 'active',
            'isolir_by' => null,
            'isolir_at' => null,
        ]);

        // Change back to normal package profile in MikroTik if NAS is set
        if ($pelanggan->nas && $pelanggan->paket) {
            $profileName = $pelanggan->paket->mikrotik_profile ?: $pelanggan->paket->nama;
            $this->mikrotikService->changePppoeProfile($pelanggan->nas, $pelanggan->username_pppoe, $profileName);
        }

        IsolirLog::create([
            'pelanggan_id' => $pelanggan->id,
            'aksi'         => 'aktifkan',
            'metode'       => 'manual',
            'user_id'      => auth()->id(),
            'alasan'       => $validated['alasan'] ?? 'Diaktifkan manual oleh ' . auth()->user()->name,
        ]);

        return back()->with('success', 'Pelanggan ' . $pelanggan->nama . ' berhasil diaktifkan.');
    }

    public function log(Request $request)
    {
        $logs = IsolirLog::with(['pelanggan', 'user', 'invoice'])
            ->latest()
            ->paginate(30);
        return view('isolir.log', compact('logs'));
    }
}
