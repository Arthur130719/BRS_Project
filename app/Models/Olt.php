<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Olt extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama', 'ip_address', 'model', 'lokasi',
        'total_port', 'status', 'uptime',
    ];

    public function onus()
    {
        return $this->hasMany(Onu::class);
    }

    public function pelanggans()
    {
        return $this->hasMany(Pelanggan::class);
    }

    public function getPortsOnlineAttribute(): int
    {
        return $this->onus()->where('status', 'online')->count();
    }

    public function getPortsOfflineAttribute(): int
    {
        return $this->onus()->whereIn('status', ['offline', 'weak'])->count();
    }
}
