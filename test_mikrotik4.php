<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $pelanggan = \App\Models\Pelanggan::where('username_pppoe', 'tes-pelanggan')->first();
    echo "NAS ID: " . $pelanggan->nas_id . "\n";
    echo "Is NAS loaded? " . ($pelanggan->relationLoaded('nas') ? 'Yes' : 'No') . "\n";
    $nas = $pelanggan->nas;
    echo "NAS Name: " . ($nas ? $nas->nama : 'NULL') . "\n";
    
    if ($pelanggan->nas) {
        echo "Calling changePppoeProfile...\n";
        $svc = app(\App\Services\MikrotikService::class);
        $result = $svc->changePppoeProfile($pelanggan->nas, $pelanggan->username_pppoe, 'isolir');
        var_dump($result);
    } else {
        echo "No NAS found!\n";
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
