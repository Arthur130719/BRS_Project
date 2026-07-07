<?php

namespace App\Http\Controllers;

use App\Models\IsolirLog;
use App\Models\Notifikasi;
use App\Models\Pelanggan;
use Illuminate\Http\Request;

class IsolirController extends Controller
{
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
