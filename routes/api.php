<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Pelanggan;
use App\Models\Paket;
use App\Models\Invoice;
use App\Models\Ticket;
use App\Models\Notifikasi;
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/paket', function () {
    return Paket::all();
});

Route::post('/pelanggan/login', function (Request $request) {
    $request->validate([
        'username_pppoe' => 'required|string',
        'password_pppoe' => 'required|string',
    ]);

    $pelanggan = Pelanggan::where('username_pppoe', $request->username_pppoe)
        ->where('password_pppoe', $request->password_pppoe)
        ->first();

    if (!$pelanggan) {
        return response()->json(['message' => 'Username atau Password tidak valid.'], 401);
    }

    if ($pelanggan->status === 'inactive') {
        return response()->json(['message' => 'Akun belum aktif.'], 403);
    }

    $token = $pelanggan->createToken('pelanggan-auth')->plainTextToken;

    return response()->json([
        'message' => 'Login berhasil',
        'token' => $token,
        'user' => [
            'id' => $pelanggan->id,
            'nama' => $pelanggan->nama,
            'username_pppoe' => $pelanggan->username_pppoe,
        ]
    ]);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/pelanggan/ping', function (Request $request) {
        return response()->json(['status' => 'ok']);
    });

    Route::get('/pelanggan/dashboard', function (Request $request) {
        /** @var \App\Models\Pelanggan $pelanggan */
        $pelanggan = $request->user();
        $pelanggan->load('paket');
        
        // Mencari tagihan terakhir yang belum lunas
        $tagihanTerakhir = Invoice::where('pelanggan_id', $pelanggan->id)
            ->where('status', '!=', 'paid')
            ->orderBy('tgl_jatuh_tempo', 'asc')
            ->first();

        $tagihanData = null;
        if ($tagihanTerakhir) {
            $jatuhTempo = Carbon::parse($tagihanTerakhir->tgl_jatuh_tempo);
            $sisaHari = now()->diffInDays($jatuhTempo, false);
            // Jika sisaHari negatif, berarti sudah lewat jatuh tempo
            $sisaHari = (int) ceil($sisaHari);
            
            $tagihanData = [
                'nominal' => $tagihanTerakhir->nominal,
                'status' => 'Belum Lunas',
                'sisa_hari' => $sisaHari < 0 ? 0 : $sisaHari,
                'is_overdue' => $sisaHari < 0
            ];
        } else {
            $tagihanData = [
                'nominal' => 0,
                'status' => 'Lunas',
                'sisa_hari' => 0,
                'is_overdue' => false
            ];
        }

        // Menghitung jumlah tiket yang masih open
        $tiketOpenCount = \App\Models\SupportTicket::where('pelanggan_id', $pelanggan->id)
            ->whereIn('status', ['open', 'in_progress'])
            ->count();

        return response()->json([
            'pelanggan' => [
                'nama' => $pelanggan->nama,
                'username_pppoe' => $pelanggan->username_pppoe,
                'status' => $pelanggan->status,
                'phone' => $pelanggan->phone,
                'phone_2' => $pelanggan->phone_2,
                'alamat' => $pelanggan->alamat,
                'latitude' => $pelanggan->latitude,
                'longitude' => $pelanggan->longitude,
                'ip_address' => $pelanggan->ip_address,
                'avatar' => $pelanggan->avatar ? asset('storage/' . $pelanggan->avatar) : null,
                'banner' => $pelanggan->banner ? asset('storage/' . $pelanggan->banner) : null,
            ],
            'paket' => $pelanggan->paket ? [
                'nama_paket' => $pelanggan->paket->nama,
                'kecepatan' => $pelanggan->paket->kecepatan_down,
                'harga' => $pelanggan->paket->harga,
            ] : null,
            'tagihan' => $tagihanData,
            'tiket_open' => $tiketOpenCount,
        ]);
    });

    Route::get('/pelanggan/live-session', function (Request $request) {
        return app(\App\Http\Controllers\PelangganController::class)->liveSession($request->user());
    });

    Route::post('/pelanggan/profile/update', function (Request $request) {
        $pelanggan = $request->user();
        
        $validated = $request->validate([
            'phone' => 'nullable|string|max:50',
            'phone_2' => 'nullable|string|max:50',
            'avatar' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:4096',
        ]);

        if ($request->has('phone')) {
            $pelanggan->phone = $validated['phone'];
        }
        if ($request->has('phone_2')) {
            $pelanggan->phone_2 = $validated['phone_2'];
        }

        if ($request->hasFile('avatar')) {
            if ($pelanggan->avatar) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($pelanggan->avatar);
            }
            $pelanggan->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        if ($request->hasFile('banner')) {
            if ($pelanggan->banner) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($pelanggan->banner);
            }
            $pelanggan->banner = $request->file('banner')->store('banners', 'public');
        }

        $pelanggan->save();

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
            'pelanggan' => [
                'phone' => $pelanggan->phone,
                'phone_2' => $pelanggan->phone_2,
                'avatar' => $pelanggan->avatar ? asset('storage/' . $pelanggan->avatar) : null,
                'banner' => $pelanggan->banner ? asset('storage/' . $pelanggan->banner) : null,
            ]
        ]);
    });

    Route::get('/pelanggan/invoices', function (Request $request) {
        /** @var \App\Models\Pelanggan $pelanggan */
        $pelanggan = $request->user();
        $invoices = Invoice::where('pelanggan_id', $pelanggan->id)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($invoices);
    });

    Route::get('/pelanggan/invoices/{id}/download', function (Request $request, $id) {
        /** @var \App\Models\Pelanggan $pelanggan */
        $pelanggan = $request->user();
        $invoice = Invoice::with(['pelanggan.paket'])->where('pelanggan_id', $pelanggan->id)->findOrFail($id);
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoice.pdf', compact('invoice'));
        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="invoice-' . $invoice->no_invoice . '.pdf"');
    });

    Route::get('/pelanggan/tickets', function (Request $request) {
        /** @var \App\Models\Pelanggan $pelanggan */
        $pelanggan = $request->user();
        $tickets = \App\Models\SupportTicket::where('pelanggan_id', $pelanggan->id)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($tickets);
    });

    Route::post('/pelanggan/tickets', function (Request $request) {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'alamat' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);
        
        /** @var \App\Models\Pelanggan $pelanggan */
        $pelanggan = $request->user();
        
        $ticket = \App\Models\SupportTicket::create([
            'pelanggan_id' => $pelanggan->id,
            'subject' => $validated['subject'],
            'deskripsi' => $validated['deskripsi'],
            'alamat' => $validated['alamat'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'status' => 'open',
        ]);

        Notifikasi::create([
            'type'      => 'warning',
            'title'     => 'Aduan Baru: ' . $pelanggan->nama,
            'deskripsi' => 'Judul: ' . $ticket->subject,
        ]);

        return response()->json([
            'message' => 'Tiket berhasil dibuat',
            'ticket' => $ticket
        ]);
    });

    Route::get('/pelanggan/tickets/{id}/chats', [App\Http\Controllers\TicketChatController::class, 'apiIndex']);
    Route::post('/pelanggan/tickets/{id}/chats', [App\Http\Controllers\TicketChatController::class, 'apiStore']);

    Route::post('/pelanggan/location', function (Request $request) {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        /** @var \App\Models\Pelanggan $pelanggan */
        $pelanggan = $request->user();
        $pelanggan->update([
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lokasi pelanggan berhasil diperbarui.',
            'data' => [
                'latitude' => $pelanggan->latitude,
                'longitude' => $pelanggan->longitude,
            ]
        ]);
    });

    Route::post('/pelanggan/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    });

    Route::get('/pelanggan/settings', function (Request $request) {
        $settings = \DB::table('system_settings')
            ->where('key', 'rekening_banks')
            ->first();
            
        $rekening_banks = [];
        if ($settings && $settings->value) {
            $rekening_banks = json_decode($settings->value, true) ?: [];
        }

        return response()->json([
            'rekening_banks' => $rekening_banks
        ]);
    });
});

Route::post('/pelanggan/register', function (Request $request) {
    $validated = $request->validate([
        'nama' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'alamat' => 'required|string',
        'latitude' => 'nullable|numeric|between:-90,90',
        'longitude' => 'nullable|numeric|between:-180,180',
        'paket_id' => 'required|exists:pakets,id',
    ]);

    $permohonan = \App\Models\Permohonan::create([
        'nama' => $validated['nama'],
        'phone' => $validated['phone'],
        'alamat' => $validated['alamat'],
        'latitude' => $validated['latitude'] ?? null,
        'longitude' => $validated['longitude'] ?? null,
        'paket_id' => $validated['paket_id'],
        'status' => 'pending',
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Pendaftaran berhasil dikirim. Tim BRS akan segera menghubungi Anda.',
        'data' => $permohonan
    ]);
});

// Endpoint Demonstrasi Sistem Basis Data Terdistribusi (CIE721)
Route::get('/distributed-report', [\App\Http\Controllers\DistributedQueryController::class, 'getDistributedReport']);
