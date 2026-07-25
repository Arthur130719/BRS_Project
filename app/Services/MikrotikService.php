<?php

namespace App\Services;

use App\Helpers\RouterosAPI;
use App\Models\Nas;
use Exception;
use Illuminate\Support\Facades\Log;

class MikrotikService
{
    protected $api;

    public function __construct()
    {
        $this->api = new RouterosAPI();
        // $this->api->debug = true; // Uncomment for debugging
    }

    /**
     * Connect to a specific NAS (MikroTik Router)
     */
    public function connect(Nas $nas)
    {
        if (empty($nas->ip_address) || empty($nas->api_user) || empty($nas->api_password)) {
            throw new Exception("Kredensial API MikroTik (IP, Username, Password) pada NAS {$nas->nama} tidak lengkap.");
        }

        $port = $nas->api_port ?: 8728;

        if ($this->api->connect($nas->ip_address, $nas->api_user, $nas->api_password, $port)) {
            return true;
        }

        throw new Exception("Gagal terhubung ke API MikroTik pada NAS {$nas->nama} ({$nas->ip_address}).");
    }

    /**
     * Disconnect from API
     */
    public function disconnect()
    {
        $this->api->disconnect();
    }

    /**
     * Change PPPoE Secret Profile
     */
    public function changePppoeProfile(Nas $nas, $username, $profileName)
    {
        Log::info("DEBUG MIKROTIK: changePppoeProfile dipanggil untuk username={$username}, profileName={$profileName}");
        try {
            $this->connect($nas);
            
            // Find the secret
            $this->api->write('/ppp/secret/print', false);
            $this->api->write('?name=' . $username);
            $secrets = $this->api->read();

            if (!empty($secrets) && isset($secrets[0]['.id'])) {
                $id = $secrets[0]['.id'];
                
                // Change the profile
                $this->api->write('/ppp/secret/set', false);
                $this->api->write('=.id=' . $id, false);
                $this->api->write('=profile=' . $profileName);
                $this->api->read();
                
                // Kick active connection so they reconnect with new profile
                $this->kickActivePppoe($username);
                
                $this->disconnect();
                Log::info("DEBUG MIKROTIK: Berhasil mengubah profil {$username} ke {$profileName}");
                return true;
            }
            
            $this->disconnect();
            Log::warning("MikroTik: User PPPoE {$username} tidak ditemukan saat mencoba ganti profil ke {$profileName}.");
            return false;
        } catch (Exception $e) {
            Log::error("MikroTik Error Ganti Profil: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Kick active PPPoE connection
     */
    public function kickActivePppoe($username)
    {
        $this->api->write('/ppp/active/print', false);
        $this->api->write('?name=' . $username);
        $active = $this->api->read();

        if (count($active) > 0) {
            $activeId = $active[0]['.id'];
            $this->api->write('/ppp/active/remove', false);
            $this->api->write('=.id=' . $activeId);
            $this->api->read();
            return true;
        }
        return false;
    }

    /**
     * Get real-time stats from NAS
     */
    public function getNasStats(Nas $nas)
    {
        try {
            $this->connect($nas);
            
            // Get resources (cpu, mem, uptime)
            $this->api->write('/system/resource/print');
            $resources = $this->api->read();
            
            // Get active PPPoE users
            $this->api->write('/ppp/active/print');
            $activePpp = $this->api->read();
            
            $this->disconnect();

            if (isset($resources[0])) {
                $res = $resources[0];
                $cpu = (int)($res['cpu-load'] ?? 0);
                $totalMem = (int)($res['total-memory'] ?? 1);
                $freeMem = (int)($res['free-memory'] ?? 0);
                $mem = round((($totalMem - $freeMem) / $totalMem) * 100);
                $uptime = $res['uptime'] ?? '0s';
                
                $activeUsers = count($activePpp);
                
                // Update DB to cache
                $nas->update([
                    'cpu_pct' => $cpu,
                    'mem_pct' => $mem,
                    'uptime'  => $uptime,
                    'status'  => 'online',
                ]);

                return [
                    'cpu' => $cpu,
                    'mem' => $mem,
                    'uptime' => $uptime,
                    'active_users' => $activeUsers,
                    'status' => 'online'
                ];
            }
        } catch (\Exception $e) {
            $nas->update(['status' => 'offline']);
            return [
                'cpu' => 0,
                'mem' => 0,
                'uptime' => 'Offline',
                'active_users' => 0,
                'status' => 'offline',
                'error' => $e->getMessage()
            ];
        }
        
        return [
            'cpu' => 0,
            'mem' => 0,
            'uptime' => 'Offline',
            'active_users' => 0,
            'status' => 'offline'
        ];
    }
}
