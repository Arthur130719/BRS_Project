<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\Ticket::with(['pelanggan', 'teknisi'])->latest();
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nomor_tiket', 'LIKE', "%{$search}%")
                  ->orWhere('nama_pelapor', 'LIKE', "%{$search}%")
                  ->orWhereHas('pelanggan', function($q2) use ($search) {
                      $q2->where('nama', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($request->has('kategori') && $request->kategori != '') {
            $query->where('kategori', $request->kategori);
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Tab Filtering & Midnight Reset Logic
        $tab = $request->get('tab', 'semua');
        
        if (auth()->user()->role === 'teknisi') {
            if ($tab === 'tersedia') {
                $query->whereNull('teknisi_id')->where('status', '!=', 'Selesai');
            } elseif ($tab === 'tugas_saya') {
                $query->where('teknisi_id', auth()->id())->where('status', '!=', 'Selesai');
            } elseif ($tab === 'riwayat') {
                $query->where('teknisi_id', auth()->id())->where('status', 'Selesai');
            } else {
                // Default view (Semua Aktif): Active + Today's Selesai
                $query->where(function($q) {
                    $q->where('status', '!=', 'Selesai')
                      ->orWhere(function($q2) {
                          $q2->where('status', 'Selesai')->whereDate('updated_at', \Carbon\Carbon::today());
                      });
                });
            }
        } else {
            // Admin / Kasir
            if ($tab === 'belum_diambil') {
                $query->whereNull('teknisi_id')->where('status', '!=', 'Selesai');
            } elseif ($tab === 'sedang_dikerjakan') {
                $query->whereNotNull('teknisi_id')->where('status', '!=', 'Selesai');
            } elseif ($tab === 'arsip') {
                $query->where('status', 'Selesai');
            } else {
                // Default view (Semua Job Order): Active + Today's Selesai
                $query->where(function($q) {
                    $q->where('status', '!=', 'Selesai')
                      ->orWhere(function($q2) {
                          $q2->where('status', 'Selesai')->whereDate('updated_at', \Carbon\Carbon::today());
                      });
                });
            }
        }

        $tickets = $query->paginate(15)->withQueryString();
        
        return view('ticket.index', compact('tickets', 'tab'));
    }

    public function create()
    {
        if (auth()->user()->role === 'teknisi') abort(403, 'Unauthorized');
        
        $pelanggans = \App\Models\Pelanggan::all();
        $teknisis = \App\Models\User::where('role', 'teknisi')->orWhere('role', 'admin')->get();
        return view('ticket.create', compact('pelanggans', 'teknisis'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role === 'teknisi') abort(403, 'Unauthorized');

        $validated = $request->validate([
            'kategori' => 'required|in:PSB,Gangguan,Cabut Modem,Lainnya',
            'pelanggan_id' => 'nullable|exists:pelanggans,id',
            'nama_pelapor' => 'nullable|string|max:255',
            'no_hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'deskripsi_pekerjaan' => 'required|string',
            'jadwal_kunjungan' => 'nullable|date',
            'teknisi_id' => 'nullable|exists:users,id',
        ]);

        $prefixMap = [
            'PSB' => 'PSB',
            'Gangguan' => 'GGN',
            'Cabut Modem' => 'CBT',
            'Lainnya' => 'LLN',
        ];
        $prefix = $prefixMap[$validated['kategori']] ?? 'JOB';

        $validated['nomor_tiket'] = $prefix . '-' . date('ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        $validated['status'] = 'Pending';

        \App\Models\Ticket::create($validated);

        return redirect()->route('tickets.index')->with('success', 'Tiket / Job Order berhasil dibuat.');
    }

    public function show(string $id)
    {
        $ticket = \App\Models\Ticket::with(['pelanggan', 'teknisi'])->findOrFail($id);
        $teknisis = \App\Models\User::where('role', 'teknisi')->orWhere('role', 'admin')->get();
        return view('ticket.show', compact('ticket', 'teknisis'));
    }

    public function edit(string $id)
    {
        if (auth()->user()->role === 'teknisi') abort(403, 'Unauthorized');

        $ticket = \App\Models\Ticket::findOrFail($id);
        $pelanggans = \App\Models\Pelanggan::all();
        $teknisis = \App\Models\User::where('role', 'teknisi')->orWhere('role', 'admin')->get();
        return view('ticket.edit', compact('ticket', 'pelanggans', 'teknisis'));
    }

    public function update(Request $request, string $id)
    {
        $ticket = \App\Models\Ticket::findOrFail($id);

        $validated = $request->validate([
            'kategori' => 'required|in:PSB,Gangguan,Cabut Modem,Lainnya',
            'pelanggan_id' => 'nullable|exists:pelanggans,id',
            'nama_pelapor' => 'nullable|string|max:255',
            'no_hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'deskripsi_pekerjaan' => 'required|string',
            'jadwal_kunjungan' => 'nullable|date',
            'status' => 'required|in:Pending,Proses,Selesai',
            'penggunaan_alat' => 'nullable|string',
            'teknisi_id' => 'nullable|exists:users,id',
            'nama_partner' => 'nullable|string|max:255',
        ]);

        $ticket->update($validated);

        return redirect()->route('tickets.index')->with('success', 'Tiket / Job Order berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        if (auth()->user()->role === 'teknisi') abort(403, 'Unauthorized');

        $ticket = \App\Models\Ticket::findOrFail($id);
        $ticket->delete();

        return redirect()->route('tickets.index')->with('success', 'Tiket / Job Order berhasil dihapus.');
    }
}
