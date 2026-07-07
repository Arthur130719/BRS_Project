<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nas extends Model
{
    use HasFactory;

    protected $table = 'nas';

    protected $fillable = [
        'kode', 'nama', 'ip_address', 'model', 'lokasi',
        'status', 'uptime', 'cpu_pct', 'mem_pct',
    ];

    public function pelanggans()
    {
        return $this->hasMany(Pelanggan::class);
    }

    public function radiusSessions()
    {
        return $this->hasMany(RadiusSession::class, 'nas_id', 'kode');
    }
}
