<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\Ticket;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PermohonanController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasRole(['admin', 'kasir'])) {
            abort(403, 'Akses ditolak.');
        }

        $permohonans = Permohonan::with('paket')
            ->where('status', 'pending')
            ->latest()
            ->paginate(15);

        return view('permohonan.index', compact('permohonans'));
    }

    public function accept(Permohonan $permohonan)
    {
        if (!auth()->user()->hasRole(['admin', 'kasir'])) {
            abort(403, 'Akses ditolak.');
        }

        if ($permohonan->status !== 'pending') {
            return redirect()->back()->with('error', 'Permohonan sudah diproses.');
        }

        // Update status permohonan
        $permohonan->update(['status' => 'accepted']);

        // Buat Job Order (Ticket) untuk teknisi
        $ticket = Ticket::create([
            'nomor_tiket' => 'PSB-' . strtoupper(uniqid()),
            'kategori' => 'PSB',
            'nama_pelapor' => $permohonan->nama,
            'no_hp' => $permohonan->phone,
            'alamat' => $permohonan->alamat,
            'latitude' => $permohonan->latitude,
            'longitude' => $permohonan->longitude,
            'deskripsi_pekerjaan' => "Pemasangan Baru (PSB).\nPaket: " . ($permohonan->paket->nama ?? 'Unknown') . "\nAlamat: " . $permohonan->alamat,
            'status' => 'Pending',
            'jadwal_kunjungan' => Carbon::now()->addDay(),
        ]);

        Notifikasi::create([
            'type'      => 'info',
            'title'     => 'Job Order Baru (PSB)',
            'deskripsi' => 'PSB untuk ' . $permohonan->nama . ' telah dibuat. Menunggu teknisi.',
        ]);

        return redirect()->route('permohonan.index')->with('success', 'Permohonan berhasil di-ACC. Job Order telah dibuat untuk Teknisi.');
    }

    public function reject(Permohonan $permohonan)
    {
        if (!auth()->user()->hasRole(['admin', 'kasir'])) {
            abort(403, 'Akses ditolak.');
        }

        $permohonan->update(['status' => 'rejected']);

        return redirect()->route('permohonan.index')->with('success', 'Permohonan berhasil ditolak.');
    }
}
