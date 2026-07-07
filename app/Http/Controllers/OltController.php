<?php

namespace App\Http\Controllers;

use App\Models\Olt;
use App\Models\Onu;
use Illuminate\Http\Request;

class OltController extends Controller
{
    public function index()
    {
        $olts = Olt::withCount(['onus', 'onus as onus_online_count' => fn($q) => $q->where('status', 'online')])->get();
        $onus = Onu::with(['olt', 'pelanggan'])->latest()->paginate(20);
        return view('olt.index', compact('olts', 'onus'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'       => 'required|max:100',
            'ip_address' => 'required|max:45',
            'model'      => 'nullable|max:100',
            'lokasi'     => 'nullable|max:200',
            'total_port' => 'nullable|integer|min:1|max:128',
        ]);
        Olt::create($validated);
        return redirect()->route('olt.index')->with('success', 'OLT berhasil ditambahkan.');
    }

    public function update(Request $request, Olt $olt)
    {
        $validated = $request->validate([
            'nama'       => 'required|max:100',
            'ip_address' => 'required|max:45',
            'model'      => 'nullable|max:100',
            'lokasi'     => 'nullable|max:200',
            'total_port' => 'nullable|integer|min:1|max:128',
            'status'     => 'required|in:online,offline,maintenance',
        ]);
        $olt->update($validated);
        return redirect()->route('olt.index')->with('success', 'OLT berhasil diperbarui.');
    }

    public function destroy(Olt $olt)
    {
        $olt->delete();
        return redirect()->route('olt.index')->with('success', 'OLT berhasil dihapus.');
    }

    public function onus(Olt $olt)
    {
        $onus = $olt->onus()->with('pelanggan')->paginate(20);
        return view('olt.onus', compact('olt', 'onus'));
    }

    public function rebootOnu(Onu $onu)
    {
        // In a real system, this would send SNMP command to the OLT
        // For now, we simulate by toggling status temporarily
        $onu->update(['status' => 'offline']);

        // Log the action as a notification
        \App\Models\Notifikasi::create([
            'type'      => 'warning',
            'title'     => 'Reboot ONU: ' . ($onu->serial_number ?? $onu->sn ?? '-'),
            'deskripsi' => 'Reboot dilakukan oleh ' . auth()->user()->name .
                           ' pada ONU milik ' . ($onu->pelanggan?->nama ?? 'tidak diketahui'),
        ]);

        return back()->with('success', 'Perintah reboot ONU ' . ($onu->serial_number ?? $onu->sn ?? '') . ' telah dikirim.');
    }
}
