<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'title', 'deskripsi', 'is_read', 'target_role', 'url'
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function getIconAttribute(): string
    {
        return match($this->type) {
            'danger'  => 'fa-triangle-exclamation',
            'warning' => 'fa-bell',
            'success' => 'fa-circle-check',
            default   => 'fa-circle-info',
        };
    }
}
