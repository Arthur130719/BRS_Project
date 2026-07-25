<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $guarded = ['id'];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function chats()
    {
        return $this->hasMany(TicketChat::class)->orderBy('created_at', 'asc');
    }
}
