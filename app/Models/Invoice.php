<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'pelanggan_id', 'no_invoice', 'periode',
        'nominal', 'status', 'tgl_jatuh_tempo',
        'tgl_bayar', 'keterangan',
    ];

    protected $casts = [
        'tgl_jatuh_tempo' => 'date',
        'tgl_bayar'       => 'date',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class);
    }

    public function isolirLogs()
    {
        return $this->hasMany(IsolirLog::class);
    }

    public function isOverdue(): bool
    {
        return $this->status === 'unpaid' && $this->tgl_jatuh_tempo->isPast();
    }

    public function getNominalFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->nominal, 0, ',', '.');
    }

    public static function generateNoInvoice(): string
    {
        $year  = now()->year;
        $month = now()->format('m');
        $last  = self::whereYear('created_at', $year)->whereMonth('created_at', now()->month)->count() + 1;
        return sprintf('INV-%d%s-%04d', $year, $month, $last);
    }
}
