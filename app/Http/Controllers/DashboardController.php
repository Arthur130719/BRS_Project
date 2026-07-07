<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Notifikasi;
use App\Models\Paket;
use App\Models\Pelanggan;
use App\Models\RadiusSession;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Stats: satu query agregasi, jauh lebih cepat dari 7 query terpisah ──
        $pelangganStats = Pelanggan::selectRaw("
            COUNT(*) as total,
            SUM(status = 'active')  as aktif,
            SUM(status = 'suspend') as suspend
        ")->first();

        $invoiceStats = Invoice::selectRaw("
            SUM(status = 'unpaid') as unpaid,
            SUM(status = 'unpaid' AND tgl_jatuh_tempo < CURDATE()) as overdue,
            SUM(CASE WHEN status = 'paid'
                AND MONTH(tgl_bayar) = MONTH(CURDATE())
                AND YEAR(tgl_bayar)  = YEAR(CURDATE())
                THEN nominal ELSE 0 END) as pendapatan_bulan
        ")->first();

        $stats = [
            'total_pelanggan'  => $pelangganStats->total ?? 0,
            'aktif'            => $pelangganStats->aktif ?? 0,
            'suspend'          => $pelangganStats->suspend ?? 0,
            'online_session'   => Cache::remember('radius_count', 60, fn() => RadiusSession::count()),
            'invoice_unpaid'   => $invoiceStats->unpaid ?? 0,
            'invoice_overdue'  => $invoiceStats->overdue ?? 0,
            'pendapatan_bulan' => $invoiceStats->pendapatan_bulan ?? 0,
        ];

        // ── Revenue 6 bulan: satu query GROUP BY, bukan loop 6x query ──────────
        $revenueRaw = Invoice::selectRaw("
                DATE_FORMAT(tgl_bayar, '%Y-%m') as bulan,
                DATE_FORMAT(tgl_bayar, '%b %Y')  as label,
                SUM(nominal) as amount
            ")
            ->where('status', 'paid')
            ->where('tgl_bayar', '>=', now()->subMonths(5)->startOfMonth())
            ->groupByRaw("DATE_FORMAT(tgl_bayar, '%Y-%m'), DATE_FORMAT(tgl_bayar, '%b %Y')")
            ->orderBy('bulan')
            ->get()
            ->keyBy('bulan');

        // Pastikan 6 bulan terakhir selalu muncul meski tidak ada revenue
        $revenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $date  = now()->subMonths($i);
            $key   = $date->format('Y-m');
            $revenue[] = [
                'label'  => $date->translatedFormat('M Y'),
                'amount' => $revenueRaw[$key]->amount ?? 0,
            ];
        }

        // ── Distribusi paket: cache 5 menit ─────────────────────────────────────
        $paketDistribusi = Cache::remember('paket_distribusi', 300, fn() =>
            Paket::withCount(['pelanggans' => fn($q) => $q->where('status', 'active')])
                ->get()
                ->map(fn($p) => ['label' => $p->nama, 'count' => $p->pelanggans_count])
        );

        // ── Recent data: limit ketat + only needed columns ───────────────────────
        $recentInvoices = Invoice::select(['id','no_invoice','pelanggan_id','periode','nominal','status','tgl_bayar'])
            ->with(['pelanggan:id,nama'])
            ->latest()
            ->take(5)
            ->get();

        $notifikasi      = Notifikasi::select(['id','type','title','deskripsi','created_at'])
            ->latest()->take(5)->get();

        $unreadCount     = Cache::remember('notif_unread_'.auth()->id(), 30, fn() =>
            Notifikasi::where('is_read', false)->count()
        );

        $autoIsolirLastRun = Cache::remember('auto_isolir_last_run', 60, fn() =>
            SystemSetting::get('auto_isolir_last_run', '-')
        );

        return view('dashboard.index', compact(
            'stats', 'revenue', 'paketDistribusi',
            'recentInvoices', 'notifikasi', 'unreadCount', 'autoIsolirLastRun'
        ));
    }
}
