<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RadiusSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'pelanggan_id', 'username', 'ip_address',
        'nas_id', 'uptime', 'dl_bytes', 'ul_bytes',
        'rate', 'mac_address', 'connected_at',
    ];

    protected $casts = [
        'connected_at' => 'datetime',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function getDlFormatAttribute(): string
    {
        return $this->formatBytes($this->dl_bytes);
    }

    public function getUlFormatAttribute(): string
    {
        return $this->formatBytes($this->ul_bytes);
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return round($bytes / 1073741824, 1) . ' GB';
        }
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }
        return round($bytes / 1024, 1) . ' KB';
    }
}
