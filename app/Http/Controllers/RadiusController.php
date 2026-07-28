<?php

namespace App\Http\Controllers;

use App\Models\Nas;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class RadiusController extends Controller
{
    protected $mikrotikService;

    public function __construct(MikrotikService $mikrotikService)
    {
        $this->mikrotikService = $mikrotikService;
    }

    public function index(Request $request)
    {
        // Ambil data live dari Cache (yang disuplai oleh daemon php artisan mikrotik:monitor)
        $cachedSessions = \Illuminate\Support\Facades\Cache::store('file')->get('mikrotik_live_sessions', []);
        
        $allSessions = [];
        $totalDlBytes = 0;
        $totalUlBytes = 0;
        $totalDlRate = 0;
        $totalUlRate = 0;

        foreach ($cachedSessions as $session) {
                $totalDlBytes += $session['tx-byte'] ?? 0;
                $totalUlBytes += $session['rx-byte'] ?? 0;
                $totalDlRate += $session['tx-rate'] ?? 0;
                $totalUlRate += $session['rx-rate'] ?? 0;

                // Map API response to expected structure
                $allSessions[] = [
                    'username' => $session['name'] ?? '-',
                    'ip_address' => $session['address'] ?? '-',
                    'nas_id' => $session['nas_id'] ?? 0,
                    'nas_name' => $session['nas_name'] ?? '-',
                    'uptime' => $session['uptime'] ?? '-',
                    'caller_id' => $session['caller-id'] ?? '-',
                    'service' => $session['service'] ?? '-',
                    'download' => isset($session['tx-byte']) ? $this->formatBytes($session['tx-byte']) : '0 B',
                    'upload' => isset($session['rx-byte']) ? $this->formatBytes($session['rx-byte']) : '0 B',
                    'rate' => isset($session['tx-rate']) && isset($session['rx-rate']) ? 
                              $this->formatRate($session['tx-rate']) . ' / ' . $this->formatRate($session['rx-rate']) : '-',
                ];
        }

        $sessionCollection = collect($allSessions);

        if ($request->filled('search')) {
            $s = strtolower($request->search);
            $sessionCollection = $sessionCollection->filter(function($item) use ($s) {
                return str_contains(strtolower($item['username']), $s) || 
                       str_contains(strtolower($item['ip_address']), $s) ||
                       str_contains(strtolower($item['nas_name']), $s);
            });
        }

        $totalOnline = $sessionCollection->count();

        // Manual Pagination
        $perPage = 20;
        $page = request()->get('page', 1);
        $offset = ($page - 1) * $perPage;
        
        $sessions = new LengthAwarePaginator(
            $sessionCollection->slice($offset, $perPage)->values(), // Items
            $totalOnline, // Total
            $perPage, // Per page
            $page, // Current page
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $totalDownload = $this->formatRate($totalDlRate);
        $totalUpload = $this->formatRate($totalUlRate);

        if ($request->ajax()) {
            return response()->json([
                'totalOnline' => $totalOnline,
                'totalDownload' => $totalDownload,
                'totalUpload' => $totalUpload,
                'html' => view('radius.partials.table', compact('sessions'))->render()
            ]);
        }

        return view('radius.index', compact('sessions', 'totalOnline', 'totalDownload', 'totalUpload'));
    }

    public function disconnect(Request $request, int $nas_id)
    {
        $nas = Nas::findOrFail($nas_id);
        $username = $request->username;
        
        if ($username) {
            try {
                $this->mikrotikService->connect($nas);
                $this->mikrotikService->kickActivePppoe($username);
                $this->mikrotikService->disconnect();
                return back()->with('success', "Sesi PPPoE {$username} berhasil diputus.");
            } catch (\Exception $e) {
                return back()->with('error', 'Gagal terhubung ke router: ' . $e->getMessage());
            }
        }
        
        return back()->with('error', 'Gagal memutus sesi: Username tidak ditemukan.');
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $bytes = (float) $bytes;
        if ($bytes <= 0) return '0 B';
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    private function formatRate($bps, $precision = 1)
    {
        $bps = (float) $bps;
        if ($bps <= 0) return '0 bps';
        $units = array('bps', 'Kbps', 'Mbps', 'Gbps');
        $pow = floor(($bps ? log($bps) : 0) / log(1000));
        $pow = min($pow, count($units) - 1);
        $bps /= pow(1000, $pow);
        return round($bps, $precision) . ' ' . $units[$pow];
    }
}
