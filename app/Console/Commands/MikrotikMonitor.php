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

        $apis = [];
        foreach ($nasList as $nas) {
            $this->info("Menghubungkan ke MikroTik {$nas->nama} ({$nas->ip_address})...");
            $api = new RouterosAPI();
            $api->timeout = 3; // Set timeout lebih pendek agar tidak macet lama
            if ($api->connect($nas->ip_address, $nas->api_user, $nas->api_password, $nas->api_port ?: 8728)) {
                $apis[] = ['nas' => $nas, 'api' => $api];
                $this->info(" -> Berhasil terhubung ke {$nas->nama}!");
            } else {
                $this->error(" -> Gagal terhubung ke {$nas->nama}!");
            }
        }

        if (empty($apis)) {
            $this->error("Gagal terhubung ke semua router MikroTik.");
            return;
        }
        
        $this->info("Memulai pemantauan live data multi-router (Tekan Ctrl+C untuk berhenti)...");
            
        while (true) {
            $allSessions = [];
            
            foreach ($apis as $index => $item) {
                $nas = $item['nas'];
                $api = $item['api'];
                
                try {
                    // 1. Ambil data Active Sessions
                    $api->write('/ppp/active/print');
                    $activePpp = $api->read();
                    
                    // Jika read() mengembalikan false atau error string (koneksi putus), lewati
                    if (!is_array($activePpp)) {
                        continue;
                    }
                    
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
                            
                            $allSessions[] = $session;
                        }
                    }
                    
                } catch (\Exception $e) {
                    $this->error("Koneksi terputus ke {$nas->nama}: " . $e->getMessage());
                    Log::error("MikrotikMonitor Daemon Error ({$nas->nama}): " . $e->getMessage());
                    // Kita bisa hapus router ini dari daftar jika error agar tidak memperlambat loop
                    // Tapi sementara kita biarkan untuk percobaan koneksi berikutnya
                }
            }
            
            // Simpan data gabungan semua router ke Cache selama 10 detik
            Cache::put('mikrotik_live_sessions', $allSessions, 10);

            // Beri jeda 1 detik sebelum tarik data lagi
            sleep(1);
        }
    }
}
