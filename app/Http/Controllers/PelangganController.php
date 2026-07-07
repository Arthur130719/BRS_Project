<?php

namespace App\Http\Controllers;

use App\Models\IsolirLog;
use App\Models\Nas;
use App\Models\Notifikasi;
use App\Models\Olt;
use App\Models\Paket;
use App\Models\Pelanggan;
use Illuminate\Http\Request;

class PelangganController extends Controller
{
    public function index(Request $request)
    {
        $query = Pelanggan::with(['paket', 'nas', 'olt']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('nama', 'like', "%$s%")
                ->orWhere('username_pppoe', 'like', "%$s%")
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
            'password_pppoe' => 'required|min:6',
            'nama'           => 'required|max:200',
            'phone'          => 'nullable|max:20',
            'alamat'         => 'nullable|max:500',
            'paket_id'       => 'required|exists:pakets,id',
            'nas_id'         => 'nullable|exists:nas,id',
            'olt_id'         => 'nullable|exists:olts,id',
            'ip_pool'        => 'nullable|max:50',
            'tgl_aktif'      => 'nullable|date',
            'expiry'         => 'nullable|date',
        ]);

        $pelanggan = Pelanggan::create($validated);

        Notifikasi::create([
            'type'      => 'info',
            'title'     => 'Pelanggan Baru: ' . $pelanggan->nama,
            'deskripsi' => 'PPPoE: ' . $pelanggan->username_pppoe . ' — Paket: ' . $pelanggan->paket?->nama,
        ]);

        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil ditambahkan.');
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
            'password_pppoe' => 'nullable|min:6',
            'nama'           => 'required|max:200',
            'phone'          => 'nullable|max:20',
            'alamat'         => 'nullable|max:500',
            'paket_id'       => 'required|exists:pakets,id',
            'nas_id'         => 'nullable|exists:nas,id',
            'olt_id'         => 'nullable|exists:olts,id',
            'ip_pool'        => 'nullable|max:50',
            'tgl_aktif'      => 'nullable|date',
            'expiry'         => 'nullable|date',
        ]);

        if (empty($validated['password_pppoe'])) {
            unset($validated['password_pppoe']);
        }

        $pelanggan->update($validated);

        return redirect()->route('pelanggan.index')->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    public function destroy(Pelanggan $pelanggan)
    {
        if (!auth()->user()->hasRole(['admin', 'kasir'])) {
            abort(403, 'Akses ditolak.');
        }
        $pelanggan->delete();
        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil dihapus.');
    }

    public function suspend(int $id)
    {
        $pelanggan = Pelanggan::findOrFail($id);

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

        IsolirLog::create([
            'pelanggan_id' => $pelanggan->id,
            'aksi'         => 'aktifkan',
            'metode'       => 'manual',
            'user_id'      => auth()->id(),
            'alasan'       => 'Diaktifkan manual oleh ' . auth()->user()->name,
        ]);

        return back()->with('success', 'Pelanggan ' . $pelanggan->nama . ' berhasil diaktifkan.');
    }
}
