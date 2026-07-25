<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware ActivityLogger
 *
 * Mencatat semua aksi HTTP yang memodifikasi data (POST, PUT, PATCH, DELETE)
 * ke tabel activity_logs secara otomatis.
 *
 * Ini adalah implementasi audit trail untuk akuntabilitas profesional —
 * sesuai prinsip etika profesi TI (Matkul: Isu Sosial & Keprofesian TI).
 */
class ActivityLogger
{
    /**
     * Peta route name ke deskripsi yang mudah dibaca manusia.
     */
    private array $routeDescriptions = [
        // Pelanggan
        'pelanggan.store'    => ['create',   'Pelanggan', 'Tambah pelanggan baru'],
        'pelanggan.update'   => ['update',   'Pelanggan', 'Ubah data pelanggan'],
        'pelanggan.destroy'  => ['delete',   'Pelanggan', 'Hapus pelanggan'],
        'pelanggan.suspend'  => ['suspend',  'Pelanggan', 'Isolir pelanggan manual'],
        'pelanggan.aktifkan' => ['aktifkan', 'Pelanggan', 'Aktifkan pelanggan'],

        // Invoice
        'invoice.store'   => ['create', 'Invoice', 'Buat invoice baru'],
        'invoice.update'  => ['update', 'Invoice', 'Ubah invoice'],
        'invoice.destroy' => ['delete', 'Invoice', 'Hapus invoice'],
        'invoice.lunas'   => ['update', 'Invoice', 'Tandai invoice lunas'],

        // Pembayaran
        'pembayaran.store' => ['create', 'Pembayaran', 'Catat pembayaran manual'],

        // NAS
        'nas.store'   => ['create', 'NAS', 'Tambah NAS baru'],
        'nas.update'  => ['update', 'NAS', 'Ubah data NAS'],
        'nas.destroy' => ['delete', 'NAS', 'Hapus NAS'],

        // OLT/ONU
        'olt.store'   => ['create', 'OLT',  'Tambah OLT baru'],
        'olt.update'  => ['update', 'OLT',  'Ubah data OLT'],
        'olt.destroy' => ['delete', 'OLT',  'Hapus OLT'],
        'olt.reboot'  => ['reboot', 'ONU',  'Reboot ONU'],

        // Pengguna
        'pengguna.store'   => ['create', 'Pengguna', 'Tambah pengguna baru'],
        'pengguna.update'  => ['update', 'Pengguna', 'Ubah data pengguna'],
        'pengguna.destroy' => ['delete', 'Pengguna', 'Hapus pengguna'],

        // Paket
        'paket.store'   => ['create', 'Paket', 'Tambah paket layanan'],
        'paket.update'  => ['update', 'Paket', 'Ubah paket layanan'],
        'paket.destroy' => ['delete', 'Paket', 'Hapus paket layanan'],

        // Pengaturan
        'pengaturan.update'  => ['update', 'Pengaturan', 'Ubah konfigurasi sistem'],
        'pengaturan.backup'  => ['export', 'Sistem',     'Backup database'],

        // Notifikasi
        'notifikasi.baca'      => ['update', 'Notifikasi', 'Tandai notifikasi dibaca'],
        'notifikasi.bacaSemua' => ['update', 'Notifikasi', 'Tandai semua notifikasi dibaca'],

        // Isolir override
        'isolir.isolir'    => ['suspend',  'Isolir', 'Isolir pelanggan via isolir controller'],
        'isolir.aktifkan'  => ['aktifkan', 'Isolir', 'Aktifkan pelanggan via isolir controller'],

        // RADIUS
        'radius.disconnect' => ['delete', 'RADIUS', 'Putus sesi RADIUS'],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Hanya log jika user sudah login
        if (!auth()->check()) {
            return $response;
        }

        // Hanya log method yang memodifikasi data
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return $response;
        }

        // Hanya log jika response sukses (redirect atau 2xx)
        $statusCode = $response->getStatusCode();
        if ($statusCode >= 400) {
            return $response;
        }

        try {
            $routeName = $request->route()?->getName() ?? '';
            $map = $this->routeDescriptions[$routeName] ?? null;

            if ($map) {
                [$action, $module, $description] = $map;

                // Ambil ID dari route parameter jika ada
                $routeParams = $request->route()?->parameters() ?? [];
                $modelId = array_values($routeParams)[0] ?? null;
                if (is_object($modelId)) {
                    $modelId = $modelId->id ?? null;
                }

                // Tambahkan detail nama jika rute berhubungan dengan pelanggan
                if (str_starts_with($routeName, 'pelanggan.') || str_starts_with($routeName, 'isolir.')) {
                    if ($routeName === 'pelanggan.store') {
                        $description .= ' ' . $request->input('nama');
                    } elseif ($modelId) {
                        $pelanggan = \App\Models\Pelanggan::find($modelId);
                        if ($pelanggan) {
                            $description .= ' ' . $pelanggan->nama;
                        }
                    }
                }

                ActivityLog::create([
                    'user_id'     => auth()->id(),
                    'user_name'   => auth()->user()->name,
                    'user_role'   => auth()->user()->role,
                    'action'      => $action,
                    'module'      => $module,
                    'description' => $description,
                    'model_id'    => $modelId,
                    'ip_address'  => $request->ip(),
                    'user_agent'  => substr($request->userAgent() ?? '', 0, 500),
                    'url'         => $request->fullUrl(),
                    'method'      => $request->method(),
                ]);
            }
        } catch (\Exception) {
            // Jangan crash app karena logging gagal
        }

        return $response;
    }
}
