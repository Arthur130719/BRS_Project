<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use Illuminate\Http\Request;
use App\Models\Notifikasi;

class SupportTicketController extends Controller
{
    public function index()
    {
        // Only admin and kasir can access, usually handled by middleware in web.php
        $tickets = SupportTicket::with('pelanggan')->orderBy('created_at', 'desc')->paginate(15);
        return view('support_tickets.index', compact('tickets'));
    }

    public function updateStatus(Request $request, $id)
    {
        $ticket = SupportTicket::findOrFail($id);
        $request->validate([
            'status' => 'required|in:open,in_progress,resolved'
        ]);

        $ticket->update([
            'status' => $request->status
        ]);

        return redirect()->route('support-tickets.index')->with('success', 'Status aduan berhasil diperbarui.');
    }

    public function createJobOrder($id)
    {
        $supportTicket = SupportTicket::with('pelanggan')->findOrFail($id);
        
        // Cek apakah sudah ada job order untuk aduan ini hari ini (opsional untuk mencegah duplikat)
        
        // Generate nomor tiket format AP-YYYYMMDD-0001
        $prefix = 'AP-' . date('Ymd') . '-';
        $latest = \App\Models\Ticket::where('nomor_tiket', 'like', $prefix . '%')->orderBy('id', 'desc')->first();
        
        if ($latest) {
            $lastNumber = (int) substr($latest->nomor_tiket, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        $nomorTiket = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        // Buat Job Order (Ticket)
        $ticket = \App\Models\Ticket::create([
            'nomor_tiket' => $nomorTiket,
            'kategori' => 'Gangguan',
            'pelanggan_id' => $supportTicket->pelanggan_id,
            'nama_pelapor' => $supportTicket->pelanggan->nama ?? 'Pelanggan',
            'no_hp' => $supportTicket->pelanggan->phone ?? '',
            'deskripsi_pekerjaan' => "SUMBER: Aduan Pelanggan\nJUDUL: " . $supportTicket->subject . "\n\nDESKRIPSI KENDALA:\n" . $supportTicket->deskripsi,
            'status' => 'Pending',
            'alamat' => $supportTicket->alamat,
            'latitude' => $supportTicket->latitude,
            'longitude' => $supportTicket->longitude,
        ]);

        // Update status aduan menjadi in_progress
        $supportTicket->update([
            'status' => 'in_progress'
        ]);

        Notifikasi::create([
            'type'      => 'info',
            'title'     => 'Aduan Diubah Menjadi Job Order',
            'deskripsi' => 'No: ' . $ticket->nomor_tiket . ' dari aduan pelanggan ' . ($supportTicket->pelanggan->nama ?? 'Unknown'),
        ]);

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'Job Order berhasil dibuat! Silakan tugaskan teknisi jika perlu kunjungan lapangan.');
    }
}
