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

        // ── Distribusi paket ─────────────────────────────────────
        $paketDistribusi = Paket::withCount(['pelanggans' => fn($q) => $q->where('status', 'active')])
            ->get()
            ->map(fn($p) => ['label' => $p->nama, 'count' => $p->pelanggans_count]);

        // ── Recent data: limit ketat + only needed columns ───────────────────────
        $recentInvoices = Invoice::select(['id','no_invoice','pelanggan_id','periode','nominal','status','tgl_bayar'])
            ->with(['pelanggan:id,nama'])
            ->latest()
            ->take(5)
            ->get();

        $notifikasi      = Notifikasi::select(['id','type','title','deskripsi','created_at', 'url'])
            ->where(function($q) {
                $q->whereNull('target_role')
                  ->orWhere('target_role', auth()->user()->role);
            })
            ->latest()
            ->take(5)
            ->get();

        $unreadCount     = Cache::remember('notif_unread_'.auth()->id(), 30, fn() =>
            Notifikasi::where('is_read', false)
            ->where(function($q) {
                $q->whereNull('target_role')
                  ->orWhere('target_role', auth()->user()->role);
            })->count()
        );

        $autoIsolirLastRun = Cache::remember('auto_isolir_last_run', 60, fn() =>
            SystemSetting::get('auto_isolir_last_run', '-')
        );

        return view('dashboard.index', compact(
            'stats', 'revenue', 'paketDistribusi',
            'recentInvoices', 'notifikasi', 'unreadCount', 'autoIsolirLastRun'
        ));
    }

    public function liveUpdates()
    {
        $user = auth()->user();
        
        $unreadCount = \App\Models\Notifikasi::where('is_read', false)
            ->where('type', '!=', 'chat')
            ->where(function($q) {
                $q->whereNull('target_role')
                  ->orWhere('target_role', auth()->user()->role);
            })->count();
            
        $latestNotif = \App\Models\Notifikasi::where('type', '!=', 'chat')
            ->where(function($q) {
                $q->whereNull('target_role')
                  ->orWhere('target_role', auth()->user()->role);
            })->latest('updated_at')->first();

        $chatUnreadCount = \App\Models\Notifikasi::where('is_read', false)
            ->where('type', 'chat')
            ->where(function($q) {
                $q->whereNull('target_role')
                  ->orWhere('target_role', auth()->user()->role);
            })->count();
            
        $latestChatNotif = \App\Models\Notifikasi::where('type', 'chat')
            ->where(function($q) {
                $q->whereNull('target_role')
                  ->orWhere('target_role', auth()->user()->role);
            })->latest('updated_at')->first();
        
        $ticketQuery = \App\Models\Ticket::query();
        if ($user->role === 'teknisi') {
            $ticketQuery->where('teknisi_id', $user->id);
        }
        
        $latestTicket = (clone $ticketQuery)->latest('updated_at')->first();
        $pendingTicketCount = (clone $ticketQuery)->where('status', 'Pending')->count();

        $latestPermohonan = null;
        $pendingPermohonanCount = 0;
        
        if (in_array($user->role, ['admin', 'kasir'])) {
            $latestPermohonan = \App\Models\Permohonan::latest('updated_at')->first();
            $pendingPermohonanCount = \App\Models\Permohonan::where('status', 'pending')->count();
        }

        $latestAduan = \App\Models\SupportTicket::latest('updated_at')->first();
        $pendingAduanCount = \App\Models\SupportTicket::where('status', 'open')->count();

        return response()->json([
            'unread_count' => $unreadCount,
            'latest_notif_id' => $latestNotif ? $latestNotif->id : 0,
            'latest_notif_time' => $latestNotif ? $latestNotif->updated_at->timestamp : 0,
            'latest_notif_title' => $latestNotif ? $latestNotif->title : '',
            'latest_notif_type' => $latestNotif ? $latestNotif->type : 'info',
            'latest_notif_url' => $latestNotif ? $latestNotif->url : null,
            'chat_unread_count' => $chatUnreadCount,
            'latest_chat_id' => $latestChatNotif ? $latestChatNotif->id : 0,
            'latest_chat_time' => $latestChatNotif ? $latestChatNotif->updated_at->timestamp : 0,
            'latest_chat_title' => $latestChatNotif ? $latestChatNotif->title : '',
            'latest_chat_url' => $latestChatNotif ? $latestChatNotif->url : null,
            'latest_ticket_time' => $latestTicket ? $latestTicket->updated_at->timestamp : 0,
            'pending_ticket_count' => $pendingTicketCount,
            'latest_permohonan_time' => $latestPermohonan ? $latestPermohonan->updated_at->timestamp : 0,
            'pending_permohonan_count' => $pendingPermohonanCount,
            'latest_aduan_time' => $latestAduan ? $latestAduan->updated_at->timestamp : 0,
            'pending_aduan_count' => $pendingAduanCount,
        ]);
    }
}
