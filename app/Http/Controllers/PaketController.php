<?php

namespace App\Http\Controllers;

use App\Models\Paket;
use Illuminate\Http\Request;

class PaketController extends Controller
{
    public function index()
    {
        $pakets = Paket::withCount('pelanggans')->orderBy('harga', 'asc')->get();
        return view('paket.index', compact('pakets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'           => 'required|max:100',
            'mikrotik_profile' => 'nullable|max:100',
            'kecepatan_down' => 'required|integer|min:1',
            'kecepatan_up'   => 'required|integer|min:1',
            'harga'          => 'required|numeric|min:0',
            'deskripsi'      => 'nullable|max:255',
        ]);
        Paket::create($validated);
        return redirect()->route('paket.index')->with('success', 'Paket berhasil ditambahkan.');
    }

    public function update(Request $request, Paket $paket)
    {
        $validated = $request->validate([
            'nama'           => 'required|max:100',
            'mikrotik_profile' => 'nullable|max:100',
            'kecepatan_down' => 'required|integer|min:1',
            'kecepatan_up'   => 'required|integer|min:1',
            'harga'          => 'required|numeric|min:0',
            'deskripsi'      => 'nullable|max:255',
            'is_active'      => 'boolean',
        ]);
        $paket->update($validated);
        return redirect()->route('paket.index')->with('success', 'Paket berhasil diperbarui.');
    }

    public function destroy(Paket $paket)
    {
        if ($paket->pelanggans()->count() > 0) {
            return back()->with('error', 'Paket tidak dapat dihapus karena masih digunakan oleh pelanggan.');
        }
        $paket->delete();
        return redirect()->route('paket.index')->with('success', 'Paket berhasil dihapus.');
    }
}
