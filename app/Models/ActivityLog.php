<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model ActivityLog — merepresentasikan audit trail semua aksi pengguna.
 *
 * Digunakan untuk akuntabilitas profesional dalam sistem ISP.
 * Setiap aksi penting (CRUD, login, logout, suspend, aktifkan) dicatat beserta
 * konteks lengkap: siapa, apa, kapan, dari mana.
 *
 * @property int         $id
 * @property int|null    $user_id
 * @property string|null $user_name
 * @property string|null $user_role
 * @property string      $action
 * @property string      $module
 * @property string|null $description
 * @property string|null $model_type
 * @property int|null    $model_id
 * @property array|null  $old_values
 * @property array|null  $new_values
 * @property string|null $ip_address
 * @property string|null $url
 * @property string|null $method
 */
class ActivityLog extends Model
{
    protected $fillable = [
        'user_id', 'user_name', 'user_role',
        'action', 'module', 'description',
        'model_type', 'model_id',
        'old_values', 'new_values',
        'ip_address', 'user_agent', 'url', 'method',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    // Relasi ke user (nullable, karena user bisa sudah dihapus)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── Helper static: catat log dengan mudah ──────────────────────────────

    /**
     * Catat aksi ke activity log.
     *
     * @param string      $action      Jenis aksi: create, update, delete, login, suspend, dll
     * @param string      $module      Nama modul: pelanggan, invoice, pembayaran, dll
     * @param string|null $description Deskripsi detail
     * @param mixed|null  $model       Model Eloquent yang terlibat (opsional)
     * @param array|null  $oldValues   Nilai lama (untuk update)
     * @param array|null  $newValues   Nilai baru (untuk update)
     */
    public static function record(
        string $action,
        string $module,
        ?string $description = null,
        $model = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        $user = auth()->user();

        static::create([
            'user_id'    => $user?->id,
            'user_name'  => $user?->name,
            'user_role'  => $user?->role,
            'action'     => $action,
            'module'     => $module,
            'description'=> $description,
            'model_type' => $model ? get_class($model) : null,
            'model_id'   => $model?->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => substr(request()->userAgent() ?? '', 0, 500),
            'url'        => request()->fullUrl(),
            'method'     => request()->method(),
        ]);
    }

    // ── Scopes ──────────────────────────────────────────────────────────────

    /** Filter by module */
    public function scopeModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    /** Filter by action */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /** Filter by user */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ── Accessor ────────────────────────────────────────────────────────────

    /** Icon untuk tiap jenis aksi */
    public function getActionIconAttribute(): string
    {
        return match($this->action) {
            'create'   => 'fas fa-plus-circle',
            'update'   => 'fas fa-pen',
            'delete'   => 'fas fa-trash',
            'login'    => 'fas fa-right-to-bracket',
            'logout'   => 'fas fa-right-from-bracket',
            'suspend'  => 'fas fa-lock',
            'aktifkan' => 'fas fa-lock-open',
            'reboot'   => 'fas fa-power-off',
            'export'   => 'fas fa-file-export',
            default    => 'fas fa-circle-info',
        };
    }

    /** Warna badge untuk tiap jenis aksi */
    public function getActionColorAttribute(): string
    {
        return match($this->action) {
            'create'   => 'green',
            'update'   => 'indigo',
            'delete'   => 'red',
            'login'    => 'sky',
            'logout'   => 'amber',
            'suspend'  => 'amber',
            'aktifkan' => 'green',
            'reboot'   => 'amber',
            default    => 'indigo',
        };
    }
}
