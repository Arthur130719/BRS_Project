<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\RadiusController;
use App\Http\Controllers\OltController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\NasController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\IsolirController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\PaketController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

// Auth routes (Breeze)
require __DIR__.'/auth.php';

// Redirect root to dashboard
Route::get('/', fn() => redirect()->route('dashboard'));

// ── Authenticated routes ────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // Dashboard (all roles)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/live-updates', [DashboardController::class, 'liveUpdates'])->name('live-updates');

    // Global Search (all roles)
    Route::get('/search/live', [SearchController::class, 'live'])->name('search.live');
    Route::get('/search', [SearchController::class, 'index'])->name('search');

    // Company Profile (all roles)
    Route::view('/company', 'company.profile')->name('company.profile');

    // ── Pelanggan (Admin: full CRUD | Kasir: full CRUD | Teknisi: read)
    Route::resource('pelanggan', PelangganController::class);
    Route::post('/pelanggan/{id}/suspend', [PelangganController::class, 'suspend'])->name('pelanggan.suspend');
    Route::post('/pelanggan/{id}/aktifkan', [PelangganController::class, 'aktifkan'])->name('pelanggan.aktifkan');

    // ── Permohonan (Admin + Kasir)
    Route::middleware('role:admin,kasir')->group(function () {
        Route::get('/permohonan', [\App\Http\Controllers\PermohonanController::class, 'index'])->name('permohonan.index');
        Route::post('/permohonan/{permohonan}/accept', [\App\Http\Controllers\PermohonanController::class, 'accept'])->name('permohonan.accept');
        Route::post('/permohonan/{permohonan}/reject', [\App\Http\Controllers\PermohonanController::class, 'reject'])->name('permohonan.reject');
    });

    // ── RADIUS (Admin + Teknisi)
    Route::middleware('role:admin,teknisi')->group(function () {
        Route::get('/radius', [RadiusController::class, 'index'])->name('radius.index');
        Route::post('/radius/{id}/disconnect', [RadiusController::class, 'disconnect'])->name('radius.disconnect');
    });

    // ── Ticketing (All Roles)
    Route::resource('tickets', TicketController::class);

    // ── OLT & ONU (Admin + Teknisi)
    Route::middleware('role:admin,teknisi')->group(function () {
        Route::get('/olt', [OltController::class, 'index'])->name('olt.index');
        Route::resource('olt', OltController::class)->except(['index', 'show']);
        Route::get('/olt/{olt}/onus', [OltController::class, 'onus'])->name('olt.onus');
        Route::post('/onu/{onu}/reboot', [OltController::class, 'rebootOnu'])->name('olt.reboot');
    });

    // ── NAS (Admin + Teknisi)
    Route::middleware('role:admin,teknisi')->group(function () {
        Route::get('/nas', [NasController::class, 'index'])->name('nas.index');
        Route::get('/nas/{nas}/stats', [NasController::class, 'stats'])->name('nas.stats');
        Route::resource('nas', NasController::class)->parameters(['nas' => 'nas'])->except(['index']);
    });

    // ── Billing / Invoice (Admin + Kasir)
    Route::middleware('role:admin,kasir')->group(function () {
        Route::resource('invoice', InvoiceController::class);
        Route::post('/invoice/{id}/lunas', [InvoiceController::class, 'tandaiLunas'])->name('invoice.lunas');
        Route::get('/invoice/{id}/pdf', [InvoiceController::class, 'exportPdf'])->name('invoice.pdf');
    });

    // ── Pembayaran (Admin + Kasir)
    Route::middleware('role:admin,kasir')->group(function () {
        Route::resource('pembayaran', PembayaranController::class)->only(['index', 'create', 'store', 'show']);
    });

    // ── Isolir override (Admin + Kasir)
    Route::middleware('role:admin,kasir')->group(function () {
        Route::post('/isolir/{pelanggan}/isolir', [IsolirController::class, 'isolir'])->name('isolir.isolir');
        Route::post('/isolir/{pelanggan}/aktifkan', [IsolirController::class, 'aktifkan'])->name('isolir.aktifkan');
        Route::get('/isolir/log', [IsolirController::class, 'log'])->name('isolir.log');
    });

    // ── Notifikasi (all roles)
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::post('/notifikasi/{id}/baca', [NotifikasiController::class, 'baca'])->name('notifikasi.baca');
    Route::post('/notifikasi/baca-semua', [NotifikasiController::class, 'bacaSemua'])->name('notifikasi.bacaSemua');

    // ── Bantuan / Panduan (all roles)
    Route::get('/bantuan', fn() => view('bantuan.index'))->name('bantuan.index');

    // Support Tickets (Aduan Pelanggan) - Admin & Kasir
    Route::middleware([\App\Http\Middleware\RoleMiddleware::class.':admin,kasir'])->group(function () {
        Route::get('/support-tickets', [\App\Http\Controllers\SupportTicketController::class, 'index'])->name('support-tickets.index');
        Route::post('/support-tickets/{id}/status', [\App\Http\Controllers\SupportTicketController::class, 'updateStatus'])->name('support-tickets.status');
        Route::post('/support-tickets/{id}/create-job-order', [\App\Http\Controllers\SupportTicketController::class, 'createJobOrder'])->name('support-tickets.create-job-order');
    });

    // Pengaturan System
    Route::middleware([\App\Http\Middleware\RoleMiddleware::class.':admin'])->group(function () {
        Route::resource('pengguna', PenggunaController::class);
        Route::resource('paket', PaketController::class);
        Route::get('/pengaturan',          [PengaturanController::class, 'index'])->name('pengaturan.index');
        Route::post('/pengaturan',         [PengaturanController::class, 'update'])->name('pengaturan.update');
        Route::get('/sistem/database',     [PengaturanController::class, 'dbInfo'])->name('sistem.db_info');
        Route::post('/sistem/backup',      [PengaturanController::class, 'backup'])->name('pengaturan.backup');
        Route::get('/sistem/activity-log', [ActivityLogController::class, 'index'])->name('activity_log.index');
    });
});
