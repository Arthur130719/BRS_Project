<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permohonan extends Model
{
    protected $fillable = [
        'nama',
        'phone',
        'alamat',
        'latitude',
        'longitude',
        'paket_id',
        'status',
    ];

    public function paket()
    {
        return $this->belongsTo(Paket::class);
    }
}
