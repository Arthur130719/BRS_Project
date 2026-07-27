<?php

namespace App\Http\Controllers;

use App\Models\Nas;
use Illuminate\Http\Request;

class NasController extends Controller
{
    public function index()
    {
        $nasList = Nas::withCount('pelanggans')->get();
        return view('nas.index', compact('nasList'));
    }

    public function show(Nas $nas, \App\Services\MikrotikService $mikrotikService)
    {
        // Get all active PPPoE sessions from Mikrotik
        $activeSessions = $mikrotikService->getAllActiveSessions($nas);
        
        return view('nas.show', compact('nas', 'activeSessions'));
    }

    public function stats(Nas $nas, \App\Services\MikrotikService $mikrotikService)
    {
        $stats = $mikrotikService->getNasStats($nas);
        return response()->json($stats);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode'       => 'required|unique:nas,kode|max:50',
            'nama'       => 'required|max:100',
            'ip_address' => 'required|max:45',
            'model'      => 'nullable|max:100',
            'lokasi'     => 'nullable|max:200',
            'api_user'   => 'nullable|max:255',
            'api_password' => 'nullable|max:255',
            'api_port'   => 'nullable|integer',
        ]);
        Nas::create($validated);
        return redirect()->route('nas.index')->with('success', 'NAS berhasil ditambahkan.');
    }

    public function update(Request $request, Nas $nas)
    {
        $validated = $request->validate([
            'kode'       => 'required|max:50|unique:nas,kode,' . $nas->id,
            'nama'       => 'required|max:100',
            'ip_address' => 'required|max:45',
            'model'      => 'nullable|max:100',
            'lokasi'     => 'nullable|max:200',
            'api_user'   => 'nullable|max:255',
            'api_password' => 'nullable|max:255',
            'api_port'   => 'nullable|integer',
        ]);

        if (empty($validated['api_password'])) {
            unset($validated['api_password']);
        }

        $nas->update($validated);
        return redirect()->route('nas.index')->with('success', 'NAS berhasil diperbarui.');
    }

    public function destroy(Nas $nas)
    {
        $nas->delete();
        return redirect()->route('nas.index')->with('success', 'NAS berhasil dihapus.');
    }
}
