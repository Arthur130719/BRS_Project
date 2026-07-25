<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketChat extends Model
{
    protected $guarded = ['id'];

    public function supportTicket()
    {
        return $this->belongsTo(SupportTicket::class);
    }

    public function pelangganSender()
    {
        return $this->belongsTo(Pelanggan::class, 'sender_id');
    }

    public function userSender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
