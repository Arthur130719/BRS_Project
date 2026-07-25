<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id', 'user_id', 'nominal',
        'metode', 'nama_bank', 'tgl_bayar',
        'keterangan', 'bukti_transfer',
    ];

    protected $casts = [
        'tgl_bayar' => 'date',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getMetodeLabelAttribute(): string
    {
        if ($this->metode === 'Transfer Lain' || $this->metode === 'transfer_lain') {
            return 'Transfer ' . ($this->nama_bank ?? 'Lainnya');
        }
        
        return match($this->metode) {
            'cash'             => 'Cash',
            'transfer_bca'     => 'Transfer BCA',
            'transfer_bri'     => 'Transfer BRI',
            'transfer_mandiri' => 'Transfer Mandiri',
            'transfer_bni'     => 'Transfer BNI',
            default            => $this->metode,
        };
    }

    public function getNominalFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->nominal, 0, ',', '.');
    }
}
