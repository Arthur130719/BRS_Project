<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Nas;
use App\Helpers\RouterosAPI;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MikrotikMonitor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mikrotik:monitor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor active sessions and traffic in real-time without log spam';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $nasList = Nas::where('status', 'online')->get();
        if ($nasList->isEmpty()) {
            $this->error("Tidak ada router MikroTik (NAS) yang berstatus online.");
            return;
        }

        $nas = $nasList->first(); // Mengambil router utama (bisa dimodifikasi jika multi-router)
        $this->info("Menghubungkan ke MikroTik {$nas->nama} ({$nas->ip_address})...");

        $api = new RouterosAPI();
        
        if ($api->connect($nas->ip_address, $nas->api_user, $nas->api_password, $nas->api_port ?: 8728)) {
            $this->info("Berhasil terhubung! Memulai pemantauan live data (Tekan Ctrl+C untuk berhenti)...");
            
            while (true) {
                try {
                    // 1. Ambil data Active Sessions
                    $api->write('/ppp/active/print');
                    $activePpp = $api->read();
                    
                    // 2. Ambil data Simple Queues (dynamic) untuk traffic
                    $api->write('/queue/simple/print', false);
                    $api->write('?dynamic=true');
                    $queues = $api->read();
                    
                    $queueMap = [];
                    if (is_array($queues)) {
                        foreach ($queues as $q) {
                            $queueMap[$q['name']] = $q;
                        }
                    }
                    
                    $sessions = [];
                    if (is_array($activePpp)) {
                        foreach ($activePpp as $session) {
                            $ifaceName = "<pppoe-" . ($session['name'] ?? '') . ">";
                            $session['tx-byte'] = 0;
                            $session['rx-byte'] = 0;
                            $session['tx-rate'] = 0;
                            $session['rx-rate'] = 0;

                            if (isset($queueMap[$ifaceName])) {
                                $bytes = explode('/', $queueMap[$ifaceName]['bytes'] ?? '0/0');
                                $rates = explode('/', $queueMap[$ifaceName]['rate'] ?? '0/0');
                                
                                $session['rx-byte'] = $bytes[0] ?? 0;
                                $session['tx-byte'] = $bytes[1] ?? 0;
                                $session['rx-rate'] = $rates[0] ?? 0;
                                $session['tx-rate'] = $rates[1] ?? 0;
                            }
                            $session['nas_id'] = $nas->id;
                            $session['nas_name'] = $nas->nama;
                            
                            $sessions[] = $session;
                        }
                    }
                    
                    // Simpan data array ke Cache selama 10 detik
                    // Cache ini yang akan dibaca oleh RadiusController
                    Cache::put('mikrotik_live_sessions', $sessions, 10);

                    // Beri jeda 1 detik sebelum tarik data lagi agar CPU MikroTik tetap aman
                    sleep(1);
                    
                } catch (\Exception $e) {
                    $this->error("Koneksi terputus atau terjadi error: " . $e->getMessage());
                    Log::error("MikrotikMonitor Daemon Error: " . $e->getMessage());
                    sleep(5); // Tunggu sebentar lalu coba lagi
                    break;
                }
            }
        } else {
            $this->error("Gagal terhubung ke API MikroTik.");
        }
    }
}
