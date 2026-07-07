<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PenggunaController extends Controller
{
    public function index()
    {
        $pengguna = User::latest()->get();
        return view('pengguna.index', compact('pengguna'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|max:100',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|min:8|confirmed',
            'role'      => 'required|in:admin,kasir,teknisi',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        User::create($validated);

        return redirect()->route('pengguna.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, User $pengguna)
    {
        $validated = $request->validate([
            'name'      => 'required|max:100',
            'email'     => 'required|email|unique:users,email,' . $pengguna->id,
            'password'  => 'nullable|min:8|confirmed',
            'role'      => 'required|in:admin,kasir,teknisi',
            'is_active' => 'boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active');
        $pengguna->update($validated);

        return redirect()->route('pengguna.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $pengguna)
    {
        if ($pengguna->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }
        $pengguna->delete();
        return redirect()->route('pengguna.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
