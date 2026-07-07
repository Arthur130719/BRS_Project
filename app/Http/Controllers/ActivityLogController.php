<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * ActivityLogController — Halaman Audit Trail
 *
 * Menampilkan semua aktivitas pengguna dalam sistem.
 * Mendukung akuntabilitas dan transparansi (Isu Sosial & Keprofesian TI).
 */
class ActivityLogController extends Controller
{
    /**
     * Tampilkan daftar activity log dengan filter dan pagination.
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest();

        // Filter berdasarkan modul
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        // Filter berdasarkan aksi
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter berdasarkan user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter berdasarkan tanggal
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%")
                  ->orWhere('module', 'like', "%{$search}%");
            });
        }

        $logs    = $query->paginate(25)->withQueryString();
        $users   = User::select('id', 'name', 'role')->get();
        $modules = ActivityLog::select('module')->distinct()->pluck('module');
        $actions = ActivityLog::select('action')->distinct()->pluck('action');

        // Stats
        $stats = [
            'total'   => ActivityLog::count(),
            'today'   => ActivityLog::whereDate('created_at', today())->count(),
            'this_week' => ActivityLog::whereBetween('created_at', [
                now()->startOfWeek(), now()->endOfWeek()
            ])->count(),
            'delete_actions' => ActivityLog::where('action', 'delete')->count(),
        ];

        return view('activity_log.index', compact('logs', 'users', 'modules', 'actions', 'stats'));
    }
}
