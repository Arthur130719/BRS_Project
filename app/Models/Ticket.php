<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $guarded = ['id'];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function teknisi()
    {
        return $this->belongsTo(User::class, 'teknisi_id');
    }
}
