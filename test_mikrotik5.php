<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $nas = \App\Models\Nas::find(2);
    $api = new \App\Helpers\RouterosAPI();
    
    if ($api->connect($nas->ip_address, $nas->api_user, $nas->api_password, $nas->api_port)) {
        $api->write('/system/resource/print');
        $resources = $api->read();
        
        $api->write('/ppp/active/print');
        $activePpp = $api->read();
        
        var_dump($resources);
        echo "Active PPP users: " . count($activePpp) . "\n";
        
        $api->disconnect();
    } else {
        echo "Failed to connect to Mikrotik API.\n";
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
