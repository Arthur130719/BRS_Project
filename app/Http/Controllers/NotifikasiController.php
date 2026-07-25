<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class NotifikasiController extends Controller
{
    public function index(Request $request)
    {
        $query = Notifikasi::where(function($q) {
                $q->whereNull('target_role')
                  ->orWhere('target_role', auth()->user()->role);
            })->latest('updated_at');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        } else {
            $query->where('type', '!=', 'chat');
        }

        if ($request->filled('status')) {
            $query->where('is_read', $request->status === 'read');
        }

        $notifikasis = $query->paginate(20)->withQueryString();
        $unreadCount = Notifikasi::where('is_read', false)
            ->where(function($q) {
                $q->whereNull('target_role')
                  ->orWhere('target_role', auth()->user()->role);
            })->count();

        return view('notifikasi.index', compact('notifikasis', 'unreadCount'));
    }

    public function baca(int $id)
    {
        Notifikasi::findOrFail($id)->update(['is_read' => true]);
        // Hapus cache agar badge langsung update
        Cache::forget('notif_unread_' . auth()->id());
        return back();
    }

    public function bacaSemua()
    {
        Notifikasi::where('is_read', false)
            ->where(function($q) {
                $q->whereNull('target_role')
                  ->orWhere('target_role', auth()->user()->role);
            })->update(['is_read' => true]);
        // Hapus cache agar badge langsung update
        Cache::forget('notif_unread_' . auth()->id());
        return back()->with('success', 'Semua notifikasi ditandai sebagai dibaca.');
    }
}
