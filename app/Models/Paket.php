<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paket extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama', 'mikrotik_profile', 'kecepatan_down', 'kecepatan_up', 'harga', 'deskripsi', 'is_active', 'tampil_di_web'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'tampil_di_web' => 'boolean',
    ];

    public function pelanggans()
    {
        return $this->hasMany(Pelanggan::class);
    }

    public function getHargaFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->harga, 0, ',', '.');
    }
}
