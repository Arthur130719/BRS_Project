<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\IsolirLog;
use App\Models\Nas;
use App\Models\Notifikasi;
use App\Models\Olt;
use App\Models\Onu;
use App\Models\Paket;
use App\Models\Pelanggan;
use App\Models\RadiusSession;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Users ──────────────────────────────────────────────
        $admin = User::create([
            'name'      => 'Administrator',
            'email'     => 'admin@brs.id',
            'password'  => Hash::make('password'),
            'role'      => 'admin',
            'is_active' => true,
        ]);

        $kasir = User::create([
            'name'      => 'Kasir Utama',
            'email'     => 'kasir@brs.id',
            'password'  => Hash::make('password'),
            'role'      => 'kasir',
            'is_active' => true,
        ]);

        User::create([
            'name'      => 'Teknisi Lapangan',
            'email'     => 'teknisi@brs.id',
            'password'  => Hash::make('password'),
            'role'      => 'teknisi',
            'is_active' => true,
        ]);

        // ── Paket ──────────────────────────────────────────────
        $pakets = [
            ['nama' => 'Home 10 Mbps',    'kecepatan_down' => 10,  'kecepatan_up' => 5,   'harga' => 150000],
            ['nama' => 'Home 20 Mbps',    'kecepatan_down' => 20,  'kecepatan_up' => 10,  'harga' => 200000],
            ['nama' => 'Home 50 Mbps',    'kecepatan_down' => 50,  'kecepatan_up' => 25,  'harga' => 300000],
            ['nama' => 'Bisnis 100 Mbps', 'kecepatan_down' => 100, 'kecepatan_up' => 50,  'harga' => 500000],
            ['nama' => 'Bisnis 200 Mbps', 'kecepatan_down' => 200, 'kecepatan_up' => 100, 'harga' => 800000],
        ];
        foreach ($pakets as $p) Paket::create($p);

        $p10  = Paket::where('nama', 'Home 10 Mbps')->first();
        $p20  = Paket::where('nama', 'Home 20 Mbps')->first();
        $p50  = Paket::where('nama', 'Home 50 Mbps')->first();
        $p100 = Paket::where('nama', 'Bisnis 100 Mbps')->first();
        $p200 = Paket::where('nama', 'Bisnis 200 Mbps')->first();

        // ── NAS ──────────────────────────────────────────────
        $nas1 = Nas::create(['kode' => 'MK-CORE-01', 'nama' => 'Core Router 1', 'ip_address' => '192.168.1.1', 'model' => 'Mikrotik CCR1036', 'lokasi' => 'Rack-1', 'status' => 'online', 'uptime' => '45d 12h', 'cpu_pct' => 23, 'mem_pct' => 41]);
        $nas2 = Nas::create(['kode' => 'MK-CORE-02', 'nama' => 'Core Router 2', 'ip_address' => '192.168.2.1', 'model' => 'Mikrotik CCR2004', 'lokasi' => 'Rack-1', 'status' => 'online', 'uptime' => '38d 6h',  'cpu_pct' => 18, 'mem_pct' => 35]);
        $nas3 = Nas::create(['kode' => 'MK-DIST-01', 'nama' => 'Distribution 1', 'ip_address' => '10.0.0.1',   'model' => 'Mikrotik RB3011',  'lokasi' => 'Rack-2', 'status' => 'online', 'uptime' => '22d 3h',  'cpu_pct' => 45, 'mem_pct' => 58]);

        // ── OLT ──────────────────────────────────────────────
        $olt1 = Olt::create(['nama' => 'ZTE C320 Rack-1', 'ip_address' => '192.168.10.1', 'model' => 'ZTE C320', 'lokasi' => 'Rack-1', 'total_port' => 16, 'status' => 'online', 'uptime' => '60d 2h']);
        $olt2 = Olt::create(['nama' => 'ZTE C300 Rack-2', 'ip_address' => '192.168.10.2', 'model' => 'ZTE C300', 'lokasi' => 'Rack-2', 'total_port' => 16, 'status' => 'online', 'uptime' => '30d 8h']);

        // ── Pelanggan ──────────────────────────────────────────────
        $pelData = [
            ['username_pppoe' => 'budi@netcore',   'nama' => 'Budi Santoso',    'phone' => '0812-3456-7890', 'paket_id' => $p20->id,  'nas_id' => $nas1->id, 'olt_id' => $olt1->id, 'ip_address' => '10.10.1.5',  'status' => 'active',  'tgl_aktif' => '2024-01-15', 'expiry' => now()->addDays(15)],
            ['username_pppoe' => 'siti@netcore',   'nama' => 'Siti Rahayu',     'phone' => '0813-2345-6789', 'paket_id' => $p50->id,  'nas_id' => $nas1->id, 'olt_id' => $olt1->id, 'ip_address' => '10.10.1.8',  'status' => 'active',  'tgl_aktif' => '2024-03-01', 'expiry' => now()->addDays(7)],
            ['username_pppoe' => 'ahmad@netcore',  'nama' => 'Ahmad Fauzi',     'phone' => '0821-3456-7891', 'paket_id' => $p100->id, 'nas_id' => $nas2->id, 'olt_id' => $olt1->id, 'ip_address' => '172.16.0.3', 'status' => 'active',  'tgl_aktif' => '2023-12-10', 'expiry' => now()->addDays(10)],
            ['username_pppoe' => 'dewi@netcore',   'nama' => 'Dewi Lestari',    'phone' => '0856-4567-8901', 'paket_id' => $p10->id,  'nas_id' => $nas1->id, 'olt_id' => $olt1->id, 'ip_address' => null,         'status' => 'suspend', 'tgl_aktif' => '2024-05-20', 'expiry' => now()->subDays(5), 'isolir_by' => 'auto', 'isolir_at' => now()->subDays(5)],
            ['username_pppoe' => 'rudi@netcore',   'nama' => 'Rudi Hermawan',   'phone' => '0877-5678-9012', 'paket_id' => $p20->id,  'nas_id' => $nas3->id, 'olt_id' => $olt2->id, 'ip_address' => '10.10.2.1',  'status' => 'active',  'tgl_aktif' => '2024-02-28', 'expiry' => now()->addDays(28)],
            ['username_pppoe' => 'ani@netcore',    'nama' => 'Ani Suryani',     'phone' => '0838-6789-0123', 'paket_id' => $p10->id,  'nas_id' => $nas3->id, 'olt_id' => $olt2->id, 'ip_address' => '10.10.2.4',  'status' => 'active',  'tgl_aktif' => '2024-06-01', 'expiry' => now()->addDays(37)],
            ['username_pppoe' => 'hendra@netcore', 'nama' => 'Hendra Kusuma',   'phone' => '0815-7890-1234', 'paket_id' => $p200->id, 'nas_id' => $nas2->id, 'olt_id' => $olt2->id, 'ip_address' => '172.16.0.7', 'status' => 'active',  'tgl_aktif' => '2024-04-15', 'expiry' => now()->addDays(15)],
            ['username_pppoe' => 'maya@netcore',   'nama' => 'Maya Putri',      'phone' => '0853-8901-2345', 'paket_id' => $p20->id,  'nas_id' => $nas1->id, 'olt_id' => $olt2->id, 'ip_address' => null,         'status' => 'inactive','tgl_aktif' => '2024-07-10', 'expiry' => now()->subDays(15)],
            ['username_pppoe' => 'joko@netcore',   'nama' => 'Joko Widodo',     'phone' => '0822-9012-3456', 'paket_id' => $p50->id,  'nas_id' => $nas3->id, 'olt_id' => $olt1->id, 'ip_address' => '10.10.3.2',  'status' => 'active',  'tgl_aktif' => '2024-08-05', 'expiry' => now()->addDays(41)],
            ['username_pppoe' => 'tini@netcore',   'nama' => 'Tini Wahyuni',    'phone' => '0895-0123-4567', 'paket_id' => $p10->id,  'nas_id' => $nas3->id, 'olt_id' => $olt1->id, 'ip_address' => '10.10.3.5',  'status' => 'active',  'tgl_aktif' => '2024-09-12', 'expiry' => now()->addDays(53)],
            ['username_pppoe' => 'wahyu@netcore',  'nama' => 'Wahyu Nugroho',   'phone' => '0812-1234-5678', 'paket_id' => $p100->id, 'nas_id' => $nas2->id, 'olt_id' => $olt2->id, 'ip_address' => '172.16.0.11','status' => 'active',  'tgl_aktif' => '2024-10-01', 'expiry' => now()->addDays(37)],
            ['username_pppoe' => 'putri@netcore',  'nama' => 'Putri Handayani', 'phone' => '0835-2345-6789', 'paket_id' => $p20->id,  'nas_id' => $nas1->id, 'olt_id' => $olt2->id, 'ip_address' => null,         'status' => 'suspend', 'tgl_aktif' => '2024-11-15', 'expiry' => now()->subDays(10), 'isolir_by' => 'auto', 'isolir_at' => now()->subDays(10)],
        ];

        $pelanggans = [];
        foreach ($pelData as $d) {
            $d['password_pppoe'] = 'pass123';
            $d['alamat']         = 'Jl. Contoh No. ' . rand(1, 99) . ', Bandung';
            $d['ip_pool']        = str_starts_with($d['ip_address'] ?? '172', '172') ? 'pool-bisnis' : 'pool-home';
            $pelanggans[] = Pelanggan::create($d);
        }

        // ── ONU ──────────────────────────────────────────────
        $onuData = [
            [$olt1->id, $pelanggans[0]->id, 'ZTEG-A1B2C3D4', '0/1/1', -19.5, -1.8, 'online',  'ZTE F660',  '5d 2h'],
            [$olt1->id, $pelanggans[1]->id, 'ZTEG-B2C3D4E5', '0/1/2', -21.2, -2.1, 'online',  'ZTE F670L', '12d 8h'],
            [$olt1->id, $pelanggans[2]->id, 'ZTEG-C3D4E5F6', '0/1/3', -18.8, -1.5, 'online',  'ZTE F680',  '30d 4h'],
            [$olt1->id, $pelanggans[3]->id, 'ZTEG-D4E5F6G7', '0/1/4', -28.9, -6.2, 'weak',    'ZTE F660',  '2h 15m'],
            [$olt2->id, $pelanggans[4]->id, 'ZTEG-E5F6G7H8', '0/2/1', -20.3, -2.0, 'online',  'ZTE F670L', '8d 6h'],
            [$olt2->id, $pelanggans[5]->id, 'ZTEG-F6G7H8I9', '0/2/2', -22.7, -2.4, 'online',  'ZTE F660',  '3d 14h'],
            [$olt2->id, $pelanggans[6]->id, 'ZTEG-G7H8I9J0', '0/2/3', -17.5, -1.2, 'online',  'ZTE F680',  '25d 9h'],
            [$olt2->id, $pelanggans[7]->id, 'ZTEG-H8I9J0K1', '0/2/4', null,  null,  'offline', 'ZTE F660',  '-'],
        ];
        foreach ($onuData as $o) {
            Onu::create(['olt_id' => $o[0], 'pelanggan_id' => $o[1], 'serial_number' => $o[2], 'port' => $o[3], 'rx_power' => $o[4], 'tx_power' => $o[5], 'status' => $o[6], 'model' => $o[7], 'uptime' => $o[8]]);
        }

        // ── RADIUS Sessions ──────────────────────────────────────────────
        $sessionData = [
            [$pelanggans[0]->id, 'budi@netcore',   '10.10.1.5',  'MK-CORE-01', '5h 23m',  2471752704,  471859200, '20 Mbps', 'AA:BB:CC:11:22:33'],
            [$pelanggans[1]->id, 'siti@netcore',   '10.10.1.8',  'MK-CORE-01', '1d 2h',   20079222989, 2254857830,'50 Mbps', 'AA:BB:CC:44:55:66'],
            [$pelanggans[2]->id, 'ahmad@netcore',  '172.16.0.3', 'MK-CORE-02', '3d 14h',  95809880064, 13209825280,'100 Mbps','DD:EE:FF:11:22:33'],
            [$pelanggans[4]->id, 'rudi@netcore',   '10.10.2.1',  'MK-DIST-01', '8h 45m',  6013607936,  817889280, '20 Mbps', 'DD:EE:FF:44:55:66'],
            [$pelanggans[5]->id, 'ani@netcore',    '10.10.2.4',  'MK-DIST-01', '22h 10m', 13311393792, 1932735283,'10 Mbps', '11:22:33:AA:BB:CC'],
            [$pelanggans[6]->id, 'hendra@netcore', '172.16.0.7', 'MK-CORE-02', '2d 6h',   263055155200,34461573120,'200 Mbps','11:22:33:DD:EE:FF'],
            [$pelanggans[8]->id, 'joko@netcore',   '10.10.3.2',  'MK-DIST-01', '4h 12m',  4080218112,  601620480, '50 Mbps', '44:55:66:AA:BB:CC'],
        ];
        foreach ($sessionData as $s) {
            RadiusSession::create(['pelanggan_id' => $s[0], 'username' => $s[1], 'ip_address' => $s[2], 'nas_id' => $s[3], 'uptime' => $s[4], 'dl_bytes' => $s[5], 'ul_bytes' => $s[6], 'rate' => $s[7], 'mac_address' => $s[8], 'connected_at' => now()->subHours(rand(1, 72))]);
        }

        // ── Invoices ──────────────────────────────────────────────
        $invoiceData = [
            [$pelanggans[0]->id, 'INV-202506-0001', 'Juni 2025',  200000, 'paid',   now()->subDays(25), now()->subDays(28)],
            [$pelanggans[1]->id, 'INV-202506-0002', 'Juni 2025',  300000, 'paid',   now()->subDays(24), now()->subDays(29)],
            [$pelanggans[2]->id, 'INV-202506-0003', 'Juni 2025',  500000, 'unpaid', now()->subDays(5),  null],
            [$pelanggans[4]->id, 'INV-202506-0004', 'Juni 2025',  200000, 'unpaid', now()->subDays(3),  null],
            [$pelanggans[5]->id, 'INV-202506-0005', 'Juni 2025',  150000, 'paid',   now()->subDays(22), now()->subDays(27)],
            [$pelanggans[6]->id, 'INV-202506-0006', 'Juni 2025',  800000, 'paid',   now()->subDays(24), now()->subDays(29)],
            [$pelanggans[8]->id, 'INV-202506-0007', 'Juni 2025',  300000, 'partial',now()->subDays(21), now()->subDays(26)],
            [$pelanggans[10]->id,'INV-202506-0008', 'Juni 2025',  500000, 'unpaid', now()->subDays(1),  null],
            [$pelanggans[9]->id, 'INV-202506-0009', 'Juni 2025',  150000, 'paid',   now()->subDays(20), now()->subDays(25)],
            [$pelanggans[3]->id, 'INV-202505-0010', 'Mei 2025',   150000, 'unpaid', now()->subDays(35), null], // Overdue, isolir
            [$pelanggans[11]->id,'INV-202505-0011', 'Mei 2025',   200000, 'unpaid', now()->subDays(40), null], // Overdue, isolir
            // Revenue sebelumnya
            [$pelanggans[0]->id, 'INV-202505-0001', 'Mei 2025',   200000, 'paid',   now()->subDays(55), now()->subDays(60)],
            [$pelanggans[6]->id, 'INV-202505-0002', 'Mei 2025',   800000, 'paid',   now()->subDays(55), now()->subDays(60)],
            [$pelanggans[2]->id, 'INV-202504-0001', 'April 2025', 500000, 'paid',   now()->subDays(85), now()->subDays(90)],
        ];

        foreach ($invoiceData as $inv) {
            Invoice::create([
                'pelanggan_id'    => $inv[0],
                'no_invoice'      => $inv[1],
                'periode'         => $inv[2],
                'nominal'         => $inv[3],
                'status'          => $inv[4],
                'tgl_jatuh_tempo' => $inv[5],
                'tgl_bayar'       => $inv[6],
            ]);
        }

        // ── Isolir Logs ──────────────────────────────────────────────
        IsolirLog::create(['pelanggan_id' => $pelanggans[3]->id,  'aksi' => 'isolir',   'metode' => 'auto',   'user_id' => null,        'alasan' => 'Tagihan jatuh tempo melewati grace period 0 hari', 'created_at' => now()->subDays(5)]);
        IsolirLog::create(['pelanggan_id' => $pelanggans[11]->id, 'aksi' => 'isolir',   'metode' => 'auto',   'user_id' => null,        'alasan' => 'Tagihan jatuh tempo melewati grace period 0 hari', 'created_at' => now()->subDays(10)]);
        IsolirLog::create(['pelanggan_id' => $pelanggans[7]->id,  'aksi' => 'isolir',   'metode' => 'manual', 'user_id' => $admin->id,  'alasan' => 'Isolir manual — pelanggan tidak dapat dihubungi', 'created_at' => now()->subDays(15)]);
        IsolirLog::create(['pelanggan_id' => $pelanggans[7]->id,  'aksi' => 'aktifkan', 'metode' => 'manual', 'user_id' => $kasir->id,  'alasan' => 'Diaktifkan kembali setelah konfirmasi pembayaran', 'created_at' => now()->subDays(14)]);

        // ── Notifikasi ──────────────────────────────────────────────
        $notifs = [
            ['danger',  'ONU Signal Lemah',           'ONU ZTEG-D4E5F6G7 (Dewi Lestari) — RX Power: -28.9 dBm (threshold: -27 dBm)',              false],
            ['warning', 'Tagihan Jatuh Tempo',         '3 pelanggan memiliki tagihan jatuh tempo: Ahmad Fauzi, Rudi Hermawan, Wahyu Nugroho',       false],
            ['info',    'Sesi Baru Terdeteksi',        'Login baru: hendra@netcore dari IP 172.16.0.7 via MK-CORE-02',                              false],
            ['warning', 'Bandwidth Abnormal',          'joko@netcore menggunakan 245 GB dalam 24 jam — melebihi FUP paket',                         false],
            ['success', 'Pembayaran Diterima',         'Invoice INV-202506-0005 (Ani Suryani) — Rp 150.000 via Cash',                               true],
            ['info',    'Pelanggan Baru Terdaftar',    'Pelanggan baru: Tini Wahyuni (tini@netcore) — Paket Home 10 Mbps',                          true],
            ['danger',  'ONU Offline',                 'ONU ZTEG-H8I9J0K1 (Maya Putri) — port 0/2/4 tidak merespons',                              true],
            ['warning', 'Auto-Isolir: Dewi Lestari',  'Pelanggan dewi@netcore diisolir otomatis. Invoice: INV-202505-0010',                         true],
            ['warning', 'Auto-Isolir: Putri Handayani','Pelanggan putri@netcore diisolir otomatis. Invoice: INV-202505-0011',                       true],
        ];
        foreach ($notifs as $n) {
            Notifikasi::create(['type' => $n[0], 'title' => $n[1], 'deskripsi' => $n[2], 'is_read' => $n[3]]);
        }

        // ── System Settings ──────────────────────────────────────────────
        $settings = [
            ['key' => 'auto_isolir_aktif',        'value' => '1',     'label' => 'Auto Isolir Aktif',          'type' => 'boolean'],
            ['key' => 'auto_isolir_grace_period',  'value' => '0',     'label' => 'Grace Period (hari)',         'type' => 'integer'],
            ['key' => 'auto_isolir_jam',           'value' => '00:01', 'label' => 'Jam Eksekusi Harian',        'type' => 'string'],
            ['key' => 'auto_isolir_last_run',      'value' => now()->subDay()->toDateTimeString(), 'label' => 'Terakhir Dijalankan', 'type' => 'string'],
            ['key' => 'auto_isolir_last_count',    'value' => '2',     'label' => 'Jumlah Diisolir Terakhir',  'type' => 'integer'],
            ['key' => 'bank_bca',                  'value' => '1234567890 a/n PT NetCORE Indonesia', 'label' => 'Rekening BCA', 'type' => 'string'],
            ['key' => 'bank_bri',                  'value' => '0987654321 a/n PT NetCORE Indonesia', 'label' => 'Rekening BRI', 'type' => 'string'],
            ['key' => 'bank_mandiri',              'value' => '',      'label' => 'Rekening Mandiri',           'type' => 'string'],
            ['key' => 'bank_bni',                  'value' => '',      'label' => 'Rekening BNI',               'type' => 'string'],
        ];
        foreach ($settings as $s) SystemSetting::create($s);
    }
}
