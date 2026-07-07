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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode'       => 'required|unique:nas,kode|max:50',
            'nama'       => 'required|max:100',
            'ip_address' => 'required|max:45',
            'model'      => 'nullable|max:100',
            'lokasi'     => 'nullable|max:200',
        ]);
        Nas::create($validated);
        return redirect()->route('nas.index')->with('success', 'NAS berhasil ditambahkan.');
    }

    public function update(Request $request, Nas $nas)
    {
        $validated = $request->validate([
            'nama'       => 'required|max:100',
            'ip_address' => 'required|max:45',
            'model'      => 'nullable|max:100',
            'lokasi'     => 'nullable|max:200',
            'status'     => 'required|in:online,offline,maintenance',
            'cpu_pct'    => 'nullable|integer|min:0|max:100',
            'mem_pct'    => 'nullable|integer|min:0|max:100',
        ]);
        $nas->update($validated);
        return redirect()->route('nas.index')->with('success', 'NAS berhasil diperbarui.');
    }

    public function destroy(Nas $nas)
    {
        $nas->delete();
        return redirect()->route('nas.index')->with('success', 'NAS berhasil dihapus.');
    }
}
