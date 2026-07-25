<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $pelanggan = \App\Models\Pelanggan::where('username_pppoe', 'tes-pelanggan')->first();
    echo "Nas: " . ($pelanggan->nas ? $pelanggan->nas->nama : 'NULL') . "\n";
    echo "Paket: " . ($pelanggan->paket ? $pelanggan->paket->nama : 'NULL') . "\n";
    
    if ($pelanggan->nas && $pelanggan->paket) {
        $profileName = $pelanggan->paket->mikrotik_profile ?: $pelanggan->paket->nama;
        echo "Profile Name evaluated: " . $profileName . "\n";
        
        $svc = app(\App\Services\MikrotikService::class);
        $result = $svc->changePppoeProfile($pelanggan->nas, $pelanggan->username_pppoe, $profileName);
        var_dump($result);
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
