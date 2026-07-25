<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use Illuminate\Http\Request;
use App\Models\Notifikasi;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        $query = SupportTicket::with('pelanggan');

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function($q) use ($search) {
                // Cari berdasarkan nama pelanggan (awalan kata)
                $q->whereHas('pelanggan', function($q2) use ($search) {
                    $q2->where('nama', 'like', "{$search}%")
                       ->orWhere('nama', 'like', "% {$search}%");
                });
                
                // Cari berdasarkan ID aduan (Nomor Tiket)
                $searchId = str_replace('#', '', $search);
                if (is_numeric($searchId)) {
                    $q->orWhere('id', $searchId);
                }
            });
        }

        if ($request->query('filter') === 'arsip') {
            // Tampilkan HANYA tiket yang resolved dan lebih dari 24 jam (Arsip)
            $query->where('status', 'resolved')
                  ->where('updated_at', '<', now()->subDay());
        } else {
            // Default: Tampilkan tiket aktif (belum selesai) atau baru selesai (< 24 jam)
            $query->where(function($q) {
                $q->where('status', '!=', 'resolved')
                  ->orWhere(function($sub) {
                      $sub->where('status', 'resolved')
                          ->where('updated_at', '>=', now()->subDay());
                  });
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
            
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
        
        $isGantiPassword = stripos($supportTicket->subject, 'Ganti Password') !== false;
        $prefix = $isGantiPassword ? 'GPW-' . date('Ymd') . '-' : 'AP-' . date('Ymd') . '-';
        
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
            'kategori' => $isGantiPassword ? 'Ganti Password Wifi' : 'Gangguan',
            'pelanggan_id' => $supportTicket->pelanggan_id,
            'support_ticket_id' => $supportTicket->id,
            'nama_pelapor' => $supportTicket->pelanggan->nama ?? 'Pelanggan',
            'no_hp' => $supportTicket->pelanggan->phone ?? '',
            'deskripsi_pekerjaan' => $isGantiPassword 
                ? "SUMBER: Permintaan Ganti Password WiFi\n" . $supportTicket->deskripsi
                : "SUMBER: Aduan Pelanggan\nJUDUL: " . $supportTicket->subject . "\n\nDESKRIPSI KENDALA:\n" . $supportTicket->deskripsi,
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

    public function destroy($id)
    {
        $ticket = SupportTicket::findOrFail($id);
        
        // Hapus aduan dari database
        $ticket->delete();

        return redirect()->back()->with('success', 'Data aduan pelanggan berhasil dihapus secara permanen.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:support_tickets,id'
        ]);

        SupportTicket::whereIn('id', $request->ids)->delete();

        return redirect()->back()->with('success', count($request->ids) . ' data aduan pelanggan berhasil dihapus secara permanen.');
    }
}
