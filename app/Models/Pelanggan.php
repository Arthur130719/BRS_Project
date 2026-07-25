<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class Pelanggan extends Authenticatable
{
    use HasApiTokens, HasFactory, SoftDeletes;

    protected $fillable = [
        'paket_id', 'nas_id', 'olt_id',
        'username_pppoe', 'password_pppoe',
        'nama', 'phone', 'alamat',
        'latitude', 'longitude',
        'ip_address', 'ip_pool',
        'status', 'isolir_by', 'isolir_at',
        'tgl_aktif', 'expiry',
        'avatar', 'banner', 'phone_2',
    ];

    protected $casts = [
        'tgl_aktif' => 'date',
        'expiry'    => 'date',
        'isolir_at' => 'datetime',
    ];

    public function paket()
    {
        return $this->belongsTo(Paket::class);
    }

    public function nas()
    {
        return $this->belongsTo(Nas::class);
    }

    public function olt()
    {
        return $this->belongsTo(Olt::class);
    }

    public function onu()
    {
        return $this->hasOne(Onu::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function pembayarans()
    {
        return $this->hasManyThrough(Pembayaran::class, Invoice::class);
    }

    public function radiusSessions()
    {
        return $this->hasMany(RadiusSession::class);
    }

    public function isolirLogs()
    {
        return $this->hasMany(IsolirLog::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspend';
    }

    public function getTagihanBelumBayarAttribute()
    {
        return $this->invoices()->where('status', 'unpaid')->count();
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'active'   => '<span class="badge badge-active">Aktif</span>',
            'suspend'  => '<span class="badge badge-suspend">Isolir</span>',
            'inactive' => '<span class="badge badge-inactive">Nonaktif</span>',
            default    => '<span class="badge">-</span>',
        };
    }
}
