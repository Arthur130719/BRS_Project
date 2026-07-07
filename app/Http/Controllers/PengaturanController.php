<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\IsolirLog;
use App\Models\Notifikasi;
use App\Models\Olt;
use App\Models\Onu;
use App\Models\Paket;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\RadiusSession;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * PengaturanController — Manajemen Konfigurasi Sistem
 *
 * Mengelola konfigurasi auto-isolir, informasi rekening bank,
 * informasi database (SBD Terdistribusi), dan backup database.
 */
class PengaturanController extends Controller
{
    /**
     * Tampilkan halaman pengaturan sistem.
     */
    public function index(): View
    {
        // pluck('value','key') → array sederhana ['key' => 'value']
        // Lebih aman dipakai di view tanpa perlu ->value
        $settings   = SystemSetting::pluck('value', 'key');
        $recentLogs = IsolirLog::with('pelanggan')->latest()->take(10)->get();

        return view('pengaturan.index', compact('settings', 'recentLogs'));
    }

    /**
     * Simpan pengaturan sistem.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'auto_isolir_enabled' => 'nullable|boolean',
            'grace_period_days'   => 'nullable|integer|min:0|max:30',
            'isolir_time'         => 'nullable|date_format:H:i',
            'bank_bca'            => 'nullable|string|max:100',
            'bank_bri'            => 'nullable|string|max:100',
            'bank_mandiri'        => 'nullable|string|max:100',
            'bank_bni'            => 'nullable|string|max:100',
        ]);

        SystemSetting::set('auto_isolir_enabled', $request->boolean('auto_isolir_enabled') ? '1' : '0');
        SystemSetting::set('grace_period_days',   $request->input('grace_period_days', 0));
        SystemSetting::set('isolir_time',          $request->input('isolir_time', '00:01'));
        SystemSetting::set('bank_bca',             $request->input('bank_bca', ''));
        SystemSetting::set('bank_bri',             $request->input('bank_bri', ''));
        SystemSetting::set('bank_mandiri',         $request->input('bank_mandiri', ''));
        SystemSetting::set('bank_bni',             $request->input('bank_bni', ''));

        return redirect()->route('pengaturan.index')
            ->with('success', 'Pengaturan berhasil disimpan.');
    }

    /**
     * Halaman informasi database — mendukung mata kuliah SBD Terdistribusi.
     * Menampilkan statistik tabel, ukuran database, dan status koneksi.
     */
    public function dbInfo(): View
    {
        // Statistik record per tabel (model-based)
        $tableStats = [
            ['table' => 'users',          'label' => 'Pengguna Sistem', 'icon' => 'fa-users-gear',   'color' => 'indigo', 'count' => User::count()],
            ['table' => 'pelanggans',     'label' => 'Pelanggan',       'icon' => 'fa-users',         'color' => 'green',  'count' => Pelanggan::count()],
            ['table' => 'pakets',         'label' => 'Paket Layanan',   'icon' => 'fa-boxes-stacked', 'color' => 'sky',    'count' => Paket::count()],
            ['table' => 'invoices',       'label' => 'Invoice',         'icon' => 'fa-file-invoice',  'color' => 'amber',  'count' => Invoice::count()],
            ['table' => 'pembayarans',    'label' => 'Pembayaran',      'icon' => 'fa-money-bill',    'color' => 'green',  'count' => Pembayaran::count()],
            ['table' => 'olts',           'label' => 'OLT',             'icon' => 'fa-server',        'color' => 'indigo', 'count' => Olt::count()],
            ['table' => 'onus',           'label' => 'ONU',             'icon' => 'fa-circle-nodes',  'color' => 'sky',    'count' => Onu::count()],
            ['table' => 'radius_sessions','label' => 'Sesi RADIUS',     'icon' => 'fa-circle-dot',    'color' => 'green',  'count' => RadiusSession::count()],
            ['table' => 'isolir_logs',    'label' => 'Log Isolir',      'icon' => 'fa-lock',          'color' => 'amber',  'count' => IsolirLog::count()],
            ['table' => 'notifikasis',    'label' => 'Notifikasi',      'icon' => 'fa-bell',          'color' => 'red',    'count' => Notifikasi::count()],
            ['table' => 'activity_logs',  'label' => 'Activity Log',    'icon' => 'fa-timeline',      'color' => 'purple', 'count' => ActivityLog::count()],
        ];

        // Ukuran database dari information_schema
        $dbName    = config('database.connections.mysql.database');
        $dbSizes   = DB::select("
            SELECT
                table_name,
                ROUND((data_length + index_length) / 1024, 2) AS size_kb,
                table_rows
            FROM information_schema.tables
            WHERE table_schema = ?
            ORDER BY (data_length + index_length) DESC
        ", [$dbName]);

        $totalSizeKb = collect($dbSizes)->sum('size_kb');

        // Daftar index yang ada
        $indexes = DB::select("
            SELECT
                table_name,
                index_name,
                GROUP_CONCAT(column_name ORDER BY seq_in_index SEPARATOR ', ') AS columns,
                non_unique
            FROM information_schema.statistics
            WHERE table_schema = ?
            GROUP BY table_name, index_name, non_unique
            ORDER BY table_name, index_name
        ", [$dbName]);

        // Status Redis
        $redisStatus = 'offline';
        $redisInfo   = [];
        try {
            $redis       = app('redis')->connection();
            $info        = $redis->info();
            $redisStatus = 'online';
            $redisInfo   = [
                'version'         => $info['redis_version'] ?? '-',
                'used_memory'     => $info['used_memory_human'] ?? '-',
                'connected_clients' => $info['connected_clients'] ?? '-',
                'uptime_days'     => $info['uptime_in_days'] ?? '-',
            ];
        } catch (\Exception) {
            // Redis tidak tersedia
        }

        // Koneksi MySQL
        $mysqlVersion = DB::select('SELECT VERSION() as ver')[0]->ver ?? '-';

        return view('sistem.db_info', compact(
            'tableStats', 'dbSizes', 'totalSizeKb',
            'indexes', 'redisStatus', 'redisInfo', 'mysqlVersion', 'dbName'
        ));
    }

    /**
     * Trigger backup database ke file SQL.
     * Mendukung konsep manajemen data dalam sistem terdistribusi.
     */
    public function backup(): RedirectResponse
    {
        try {
            $filename  = 'netcore_backup_' . now()->format('Ymd_His') . '.sql';
            $path      = storage_path('app/backups/' . $filename);

            // Buat direktori jika belum ada
            if (!is_dir(storage_path('app/backups'))) {
                mkdir(storage_path('app/backups'), 0755, true);
            }

            $db   = config('database.connections.mysql.database');
            $user = config('database.connections.mysql.username');
            $pass = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');

            $cmd  = "mysqldump -h {$host} -u {$user} -p\"{$pass}\" {$db} > {$path} 2>&1";
            exec($cmd, $output, $returnCode);

            if ($returnCode === 0 && file_exists($path)) {
                // Log aksi backup
                ActivityLog::record('export', 'Sistem', "Backup database: {$filename}");

                return response()->download($path, $filename, [
                    'Content-Type' => 'application/sql',
                ])->deleteFileAfterSend(false);
            }

            return back()->with('error', 'Backup gagal. Pastikan mysqldump tersedia di server.');
        } catch (\Exception $e) {
            return back()->with('error', 'Backup error: ' . $e->getMessage());
        }
    }
}
