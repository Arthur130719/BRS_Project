<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $nas = \App\Models\Nas::find(2);
    $svc = app(\App\Services\MikrotikService::class);
    
    // Check if the service works directly
    $result = $svc->changePppoeProfile($nas, 'tes-pelanggan', 'PROFIL-YANG-TIDAK-ADA');
    var_dump($result);
    
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
