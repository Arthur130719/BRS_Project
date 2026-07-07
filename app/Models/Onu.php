<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Onu extends Model
{
    use HasFactory;

    protected $fillable = [
        'olt_id', 'pelanggan_id', 'serial_number',
        'port', 'rx_power', 'tx_power',
        'status', 'model', 'uptime',
    ];

    protected $casts = [
        'rx_power' => 'float',
        'tx_power' => 'float',
    ];

    public function olt()
    {
        return $this->belongsTo(Olt::class);
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function getSignalQualityAttribute(): string
    {
        if ($this->rx_power === null) return 'unknown';
        if ($this->rx_power >= -20) return 'excellent';
        if ($this->rx_power >= -24) return 'good';
        if ($this->rx_power >= -27) return 'weak';
        return 'poor';
    }
}
