<?php

namespace App\Http\Controllers;

use App\Models\RadiusSession;
use Illuminate\Http\Request;

class RadiusController extends Controller
{
    public function index(Request $request)
    {
        $query = RadiusSession::with('pelanggan');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('username', 'like', "%$s%")
                ->orWhere('ip_address', 'like', "%$s%")
                ->orWhere('nas_id', 'like', "%$s%")
            );
        }

        $sessions = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total_online' => RadiusSession::count(),
            'total_dl'     => RadiusSession::sum('dl_bytes'),
            'total_ul'     => RadiusSession::sum('ul_bytes'),
        ];

        return view('radius.index', compact('sessions', 'stats'));
    }

    public function disconnect(int $id)
    {
        // In a real environment, this would call Mikrotik API
        // For now, we remove the session from our mock DB
        RadiusSession::findOrFail($id)->delete();
        return back()->with('success', 'Sesi berhasil diterminasi (simulasi).');
    }
}
