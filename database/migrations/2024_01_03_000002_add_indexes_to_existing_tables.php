<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan index pada kolom yang sering digunakan untuk query filtering.
     * Optimasi performa query — konsep Sistem Basis Data Terdistribusi.
     *
     * Menggunakan IF NOT EXISTS agar idempoten (aman dijalankan berulang kali).
     */
    public function up(): void
    {
        $db = config('database.connections.mysql.database');

        // Helper: cek apakah index sudah ada
        $hasIndex = function (string $table, string $index) use ($db): bool {
            $result = DB::select(
                "SELECT COUNT(*) as cnt FROM information_schema.statistics
                 WHERE table_schema = ? AND table_name = ? AND index_name = ?",
                [$db, $table, $index]
            );
            return ($result[0]->cnt ?? 0) > 0;
        };

        // ── pelanggans ──────────────────────────────────────────────────────
        if (!$hasIndex('pelanggans', 'idx_pelanggan_status_expiry')) {
            DB::statement('ALTER TABLE `pelanggans` ADD INDEX `idx_pelanggan_status_expiry` (`status`, `expiry`)');
        }
        if (!$hasIndex('pelanggans', 'idx_pelanggan_paket')) {
            DB::statement('ALTER TABLE `pelanggans` ADD INDEX `idx_pelanggan_paket` (`paket_id`)');
        }
        if (!$hasIndex('pelanggans', 'idx_pelanggan_nas')) {
            DB::statement('ALTER TABLE `pelanggans` ADD INDEX `idx_pelanggan_nas` (`nas_id`)');
        }
        if (!$hasIndex('pelanggans', 'idx_pelanggan_status')) {
            DB::statement('ALTER TABLE `pelanggans` ADD INDEX `idx_pelanggan_status` (`status`)');
        }

        // ── invoices ────────────────────────────────────────────────────────
        if (!$hasIndex('invoices', 'idx_invoice_pelanggan_status')) {
            DB::statement('ALTER TABLE `invoices` ADD INDEX `idx_invoice_pelanggan_status` (`pelanggan_id`, `status`)');
        }
        if (!$hasIndex('invoices', 'idx_invoice_status')) {
            DB::statement('ALTER TABLE `invoices` ADD INDEX `idx_invoice_status` (`status`)');
        }
        if (!$hasIndex('invoices', 'idx_invoice_jatuh_tempo')) {
            DB::statement('ALTER TABLE `invoices` ADD INDEX `idx_invoice_jatuh_tempo` (`tgl_jatuh_tempo`)');
        }
        if (!$hasIndex('invoices', 'idx_invoice_tgl_bayar')) {
            DB::statement('ALTER TABLE `invoices` ADD INDEX `idx_invoice_tgl_bayar` (`tgl_bayar`)');
        }

        // ── pembayarans ─────────────────────────────────────────────────────
        if (!$hasIndex('pembayarans', 'idx_pembayaran_invoice')) {
            DB::statement('ALTER TABLE `pembayarans` ADD INDEX `idx_pembayaran_invoice` (`invoice_id`)');
        }
        if (!$hasIndex('pembayarans', 'idx_pembayaran_tgl')) {
            DB::statement('ALTER TABLE `pembayarans` ADD INDEX `idx_pembayaran_tgl` (`tgl_bayar`)');
        }

        // ── isolir_logs ─────────────────────────────────────────────────────
        if (!$hasIndex('isolir_logs', 'idx_isolir_pelanggan_date')) {
            DB::statement('ALTER TABLE `isolir_logs` ADD INDEX `idx_isolir_pelanggan_date` (`pelanggan_id`, `created_at`)');
        }
        if (!$hasIndex('isolir_logs', 'idx_isolir_metode')) {
            DB::statement('ALTER TABLE `isolir_logs` ADD INDEX `idx_isolir_metode` (`metode`)');
        }
        if (!$hasIndex('isolir_logs', 'idx_isolir_aksi')) {
            DB::statement('ALTER TABLE `isolir_logs` ADD INDEX `idx_isolir_aksi` (`aksi`)');
        }

        // ── notifikasis ─────────────────────────────────────────────────────
        if (!$hasIndex('notifikasis', 'idx_notif_read_date')) {
            DB::statement('ALTER TABLE `notifikasis` ADD INDEX `idx_notif_read_date` (`is_read`, `created_at`)');
        }
        if (!$hasIndex('notifikasis', 'idx_notif_type')) {
            DB::statement('ALTER TABLE `notifikasis` ADD INDEX `idx_notif_type` (`type`)');
        }

        // ── radius_sessions ─────────────────────────────────────────────────
        if (!$hasIndex('radius_sessions', 'idx_radius_username')) {
            DB::statement('ALTER TABLE `radius_sessions` ADD INDEX `idx_radius_username` (`username`)');
        }
        if (!$hasIndex('radius_sessions', 'idx_radius_nas')) {
            DB::statement('ALTER TABLE `radius_sessions` ADD INDEX `idx_radius_nas` (`nas_id`)');
        }
    }

    public function down(): void
    {
        $dropIfExists = function (string $table, string $index): void {
            $db  = config('database.connections.mysql.database');
            $cnt = DB::select(
                "SELECT COUNT(*) as cnt FROM information_schema.statistics
                 WHERE table_schema = ? AND table_name = ? AND index_name = ?",
                [$db, $table, $index]
            );
            if (($cnt[0]->cnt ?? 0) > 0) {
                DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$index}`");
            }
        };

        $dropIfExists('pelanggans',      'idx_pelanggan_status_expiry');
        $dropIfExists('pelanggans',      'idx_pelanggan_paket');
        $dropIfExists('pelanggans',      'idx_pelanggan_nas');
        $dropIfExists('pelanggans',      'idx_pelanggan_status');
        $dropIfExists('invoices',        'idx_invoice_pelanggan_status');
        $dropIfExists('invoices',        'idx_invoice_status');
        $dropIfExists('invoices',        'idx_invoice_jatuh_tempo');
        $dropIfExists('invoices',        'idx_invoice_tgl_bayar');
        $dropIfExists('pembayarans',     'idx_pembayaran_invoice');
        $dropIfExists('pembayarans',     'idx_pembayaran_tgl');
        $dropIfExists('isolir_logs',     'idx_isolir_pelanggan_date');
        $dropIfExists('isolir_logs',     'idx_isolir_metode');
        $dropIfExists('isolir_logs',     'idx_isolir_aksi');
        $dropIfExists('notifikasis',     'idx_notif_read_date');
        $dropIfExists('notifikasis',     'idx_notif_type');
        $dropIfExists('radius_sessions', 'idx_radius_username');
        $dropIfExists('radius_sessions', 'idx_radius_nas');
    }
};
