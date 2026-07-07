@extends('layouts.app')

@section('title', 'Panduan Pengguna')
@section('page-title', 'Bantuan')
@section('breadcrumb', 'Sistem / Bantuan')

@section('content')

<style>
/* ── Role feature sections ── */
.feature-section {
    margin-bottom: 20px;
}
.feature-section-title {
    font-size: 13px;
    font-weight: 700;
    color: var(--text-1);
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
    padding-bottom: 8px;
    border-bottom: 1px solid var(--border);
}
.feature-list {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.feature-list li {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    font-size: 13px;
    color: var(--text-2);
    line-height: 1.5;
}
.feature-list li i {
    margin-top: 2px;
    font-size: 11px;
    flex-shrink: 0;
    width: 14px;
    text-align: center;
}
/* ── Tab buttons ── */
.tab-group {
    display: flex;
    gap: 4px;
    background: var(--bg-elevated);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 4px;
    flex-wrap: wrap;
}
.tab-btn {
    padding: 6px 16px;
    border-radius: calc(var(--radius) - 2px);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    background: transparent;
    color: var(--text-3);
    transition: all var(--transition);
    display: flex;
    align-items: center;
    gap: 6px;
}
.tab-btn:hover { color: var(--text-1); background: var(--bg-surface); }
.tab-btn.active { background: var(--indigo); color: white; }
/* ── Flowchart ── */
.flow-step {
    display: flex;
    gap: 14px;
    align-items: flex-start;
    position: relative;
}
.flow-step:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 17px;
    top: 36px;
    bottom: -10px;
    width: 2px;
    background: var(--border);
}
.flow-num {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 700;
    flex-shrink: 0;
    border: 2px solid;
}
.flow-body {
    flex: 1;
    padding: 8px 12px;
    background: var(--bg-elevated);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    margin-bottom: 10px;
}
.flow-body-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-1);
}
.flow-body-desc {
    font-size: 12px;
    color: var(--text-3);
    margin-top: 2px;
}
/* ── FAQ accordion ── */
.faq-item {
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    margin-bottom: 8px;
    transition: border-color var(--transition);
}
.faq-item:hover { border-color: var(--border-light); }
.faq-question {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px;
    cursor: pointer;
    background: var(--bg-elevated);
    font-size: 13px;
    font-weight: 500;
    color: var(--text-1);
    user-select: none;
    gap: 12px;
}
.faq-question i.chevron {
    flex-shrink: 0;
    font-size: 11px;
    color: var(--text-3);
    transition: transform var(--transition);
}
.faq-answer {
    padding: 14px 16px;
    font-size: 13px;
    color: var(--text-2);
    line-height: 1.6;
    background: var(--bg-surface);
    border-top: 1px solid var(--border);
}
/* ── Role badge in header ── */
.role-badge-header {
    padding: 6px 14px;
    border-radius: var(--radius);
    font-size: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
}
</style>

{{-- ══════════════════════════════════════════════
     PAGE HEADER
══════════════════════════════════════════════ --}}
<div class="page-header">
    <div class="page-header-title">
        <h1><i class="fas fa-book-open" style="color:var(--indigo);margin-right:8px;"></i>Panduan Pengguna NetCORE</h1>
        <p>Dokumentasi lengkap fitur dan alur kerja sistem manajemen ISP</p>
    </div>
    <div class="page-header-actions">
        @php $role = auth()->user()->role ?? 'admin'; @endphp
        @switch($role)
            @case('admin')
                <div class="role-badge-header" style="background:var(--indigo-dim);border:1px solid rgba(99,102,241,0.3);color:#a5b4fc;">
                    <i class="fas fa-shield-alt"></i> Admin
                </div>
                @break
            @case('kasir')
                <div class="role-badge-header" style="background:var(--green-d);border:1px solid rgba(16,185,129,0.3);color:#6ee7b7;">
                    <i class="fas fa-cash-register"></i> Kasir
                </div>
                @break
            @case('teknisi')
                <div class="role-badge-header" style="background:var(--amber-d);border:1px solid rgba(245,158,11,0.3);color:#fcd34d;">
                    <i class="fas fa-tools"></i> Teknisi
                </div>
                @break
        @endswitch
    </div>
</div>

{{-- ══════════════════════════════════════════════
     ROLE TABS + CONTENT
══════════════════════════════════════════════ --}}
<div class="card" style="margin-bottom:20px;" x-data="{ tab: '{{ $role === 'admin' ? 'all' : $role }}' }">

    {{-- Tab buttons --}}
    <div class="card-header" style="flex-wrap:wrap;gap:10px;">
        <div class="card-title">
            <i class="fas fa-users" style="margin-right:6px;color:var(--indigo);"></i>
            Fitur per Peran
        </div>
        <div class="tab-group">
            <button class="tab-btn" :class="tab === 'all' ? 'active' : ''" @click="tab = 'all'">
                <i class="fas fa-th"></i> Semua
            </button>
            <button class="tab-btn" :class="tab === 'admin' ? 'active' : ''" @click="tab = 'admin'">
                <i class="fas fa-shield-alt"></i> Admin
            </button>
            <button class="tab-btn" :class="tab === 'kasir' ? 'active' : ''" @click="tab = 'kasir'">
                <i class="fas fa-cash-register"></i> Kasir
            </button>
            <button class="tab-btn" :class="tab === 'teknisi' ? 'active' : ''" @click="tab = 'teknisi'">
                <i class="fas fa-tools"></i> Teknisi
            </button>
        </div>
    </div>

    <div class="card-body">

        {{-- ────────────────────────────────────────
             TAB: SEMUA (overview perbandingan)
        ──────────────────────────────────────── --}}
        <div x-show="tab === 'all'" x-transition>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">

                {{-- Admin column --}}
                <div style="background:var(--indigo-dim);border:1px solid rgba(99,102,241,0.2);border-radius:var(--radius);padding:16px;">
                    <div style="font-size:13px;font-weight:700;color:#a5b4fc;margin-bottom:12px;display:flex;align-items:center;gap:6px;">
                        <i class="fas fa-shield-alt"></i> Admin
                        <span style="font-size:10px;font-weight:500;color:var(--text-4);margin-left:auto;">Akses Penuh</span>
                    </div>
                    <ul class="feature-list">
                        <li><i class="fas fa-check" style="color:var(--green);"></i> Dashboard & statistik</li>
                        <li><i class="fas fa-check" style="color:var(--green);"></i> Kelola pelanggan</li>
                        <li><i class="fas fa-check" style="color:var(--green);"></i> Invoice & pembayaran</li>
                        <li><i class="fas fa-check" style="color:var(--green);"></i> Monitor jaringan</li>
                        <li><i class="fas fa-check" style="color:var(--green);"></i> Kelola pengguna sistem</li>
                        <li><i class="fas fa-check" style="color:var(--green);"></i> Kelola paket layanan</li>
                        <li><i class="fas fa-check" style="color:var(--green);"></i> Pengaturan sistem</li>
                        <li><i class="fas fa-check" style="color:var(--green);"></i> Activity log & audit</li>
                    </ul>
                </div>

                {{-- Kasir column --}}
                <div style="background:var(--green-d);border:1px solid rgba(16,185,129,0.2);border-radius:var(--radius);padding:16px;">
                    <div style="font-size:13px;font-weight:700;color:#6ee7b7;margin-bottom:12px;display:flex;align-items:center;gap:6px;">
                        <i class="fas fa-cash-register"></i> Kasir
                        <span style="font-size:10px;font-weight:500;color:var(--text-4);margin-left:auto;">Operasional</span>
                    </div>
                    <ul class="feature-list">
                        <li><i class="fas fa-check" style="color:var(--green);"></i> Dashboard & statistik</li>
                        <li><i class="fas fa-check" style="color:var(--green);"></i> Kelola pelanggan</li>
                        <li><i class="fas fa-check" style="color:var(--green);"></i> Invoice & pembayaran</li>
                        <li><i class="fas fa-check" style="color:var(--green);"></i> Notifikasi sistem</li>
                        <li><i class="fas fa-times" style="color:var(--red);"></i> Monitor jaringan</li>
                        <li><i class="fas fa-times" style="color:var(--red);"></i> Kelola pengguna</li>
                        <li><i class="fas fa-times" style="color:var(--red);"></i> Pengaturan sistem</li>
                        <li><i class="fas fa-times" style="color:var(--red);"></i> Activity log</li>
                    </ul>
                </div>

                {{-- Teknisi column --}}
                <div style="background:var(--amber-d);border:1px solid rgba(245,158,11,0.2);border-radius:var(--radius);padding:16px;">
                    <div style="font-size:13px;font-weight:700;color:#fcd34d;margin-bottom:12px;display:flex;align-items:center;gap:6px;">
                        <i class="fas fa-tools"></i> Teknisi
                        <span style="font-size:10px;font-weight:500;color:var(--text-4);margin-left:auto;">Teknis</span>
                    </div>
                    <ul class="feature-list">
                        <li><i class="fas fa-check" style="color:var(--green);"></i> Dashboard & statistik</li>
                        <li><i class="fas fa-check" style="color:var(--green);"></i> Lihat detail pelanggan</li>
                        <li><i class="fas fa-check" style="color:var(--green);"></i> Monitor RADIUS & OLT</li>
                        <li><i class="fas fa-check" style="color:var(--green);"></i> ONU Reboot</li>
                        <li><i class="fas fa-check" style="color:var(--green);"></i> Notifikasi sistem</li>
                        <li><i class="fas fa-times" style="color:var(--red);"></i> Invoice & pembayaran</li>
                        <li><i class="fas fa-times" style="color:var(--red);"></i> Edit pelanggan</li>
                        <li><i class="fas fa-times" style="color:var(--red);"></i> Pengaturan sistem</li>
                    </ul>
                </div>

            </div>
        </div>

        {{-- ────────────────────────────────────────
             TAB: ADMIN
        ──────────────────────────────────────── --}}
        <div x-show="tab === 'admin'" x-transition>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

                <div class="feature-section">
                    <div class="feature-section-title">
                        <div class="stat-icon indigo" style="width:28px;height:28px;font-size:12px;margin-bottom:0;">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        Dashboard
                    </div>
                    <ul class="feature-list">
                        <li><i class="fas fa-circle" style="color:var(--indigo);font-size:6px;"></i> Melihat statistik pelanggan, invoice, pembayaran</li>
                        <li><i class="fas fa-circle" style="color:var(--indigo);font-size:6px;"></i> Chart revenue bulanan (12 bulan terakhir)</li>
                        <li><i class="fas fa-circle" style="color:var(--indigo);font-size:6px;"></i> Distribusi paket layanan (donut chart)</li>
                        <li><i class="fas fa-circle" style="color:var(--indigo);font-size:6px;"></i> Feed aktivitas terbaru</li>
                        <li><i class="fas fa-circle" style="color:var(--indigo);font-size:6px;"></i> Status pelanggan online/offline</li>
                    </ul>
                </div>

                <div class="feature-section">
                    <div class="feature-section-title">
                        <div class="stat-icon green" style="width:28px;height:28px;font-size:12px;margin-bottom:0;">
                            <i class="fas fa-id-card"></i>
                        </div>
                        Pelanggan
                    </div>
                    <ul class="feature-list">
                        <li><i class="fas fa-circle" style="color:var(--green);font-size:6px;"></i> Tambah pelanggan baru (data + PPPoE username/pass)</li>
                        <li><i class="fas fa-circle" style="color:var(--green);font-size:6px;"></i> Edit data pelanggan</li>
                        <li><i class="fas fa-circle" style="color:var(--green);font-size:6px;"></i> Hapus pelanggan</li>
                        <li><i class="fas fa-circle" style="color:var(--green);font-size:6px;"></i> Isolir (suspend) pelanggan</li>
                        <li><i class="fas fa-circle" style="color:var(--green);font-size:6px;"></i> Aktifkan kembali pelanggan</li>
                        <li><i class="fas fa-circle" style="color:var(--green);font-size:6px;"></i> Lihat detail & riwayat invoice</li>
                    </ul>
                </div>

                <div class="feature-section">
                    <div class="feature-section-title">
                        <div class="stat-icon amber" style="width:28px;height:28px;font-size:12px;margin-bottom:0;">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        Invoice & Pembayaran
                    </div>
                    <ul class="feature-list">
                        <li><i class="fas fa-circle" style="color:var(--amber);font-size:6px;"></i> Buat invoice manual / bulanan</li>
                        <li><i class="fas fa-circle" style="color:var(--amber);font-size:6px;"></i> Edit & hapus invoice</li>
                        <li><i class="fas fa-circle" style="color:var(--amber);font-size:6px;"></i> Export invoice ke PDF</li>
                        <li><i class="fas fa-circle" style="color:var(--amber);font-size:6px;"></i> Tandai lunas (pembayaran)</li>
                        <li><i class="fas fa-circle" style="color:var(--amber);font-size:6px;"></i> Catat pembayaran manual (transfer/cash)</li>
                        <li><i class="fas fa-circle" style="color:var(--amber);font-size:6px;"></i> Lihat riwayat semua pembayaran</li>
                    </ul>
                </div>

                <div class="feature-section">
                    <div class="feature-section-title">
                        <div class="stat-icon sky" style="width:28px;height:28px;font-size:12px;margin-bottom:0;">
                            <i class="fas fa-network-wired"></i>
                        </div>
                        Jaringan
                    </div>
                    <ul class="feature-list">
                        <li><i class="fas fa-circle" style="color:var(--sky);font-size:6px;"></i> Monitor RADIUS sessions aktif</li>
                        <li><i class="fas fa-circle" style="color:var(--sky);font-size:6px;"></i> Kelola OLT (Optical Line Terminal)</li>
                        <li><i class="fas fa-circle" style="color:var(--sky);font-size:6px;"></i> Monitor & kelola NAS</li>
                        <li><i class="fas fa-circle" style="color:var(--sky);font-size:6px;"></i> Lihat status ONU</li>
                        <li><i class="fas fa-circle" style="color:var(--sky);font-size:6px;"></i> Reboot ONU</li>
                    </ul>
                </div>

                <div class="feature-section">
                    <div class="feature-section-title">
                        <div class="stat-icon indigo" style="width:28px;height:28px;font-size:12px;margin-bottom:0;">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        Pengguna & Paket
                    </div>
                    <ul class="feature-list">
                        <li><i class="fas fa-circle" style="color:var(--indigo);font-size:6px;"></i> Tambah/edit/hapus akun pengguna sistem</li>
                        <li><i class="fas fa-circle" style="color:var(--indigo);font-size:6px;"></i> Set role: Admin / Kasir / Teknisi</li>
                        <li><i class="fas fa-circle" style="color:var(--indigo);font-size:6px;"></i> Kelola paket layanan internet</li>
                        <li><i class="fas fa-circle" style="color:var(--indigo);font-size:6px;"></i> Set harga & kecepatan paket</li>
                    </ul>
                </div>

                <div class="feature-section">
                    <div class="feature-section-title">
                        <div class="stat-icon red" style="width:28px;height:28px;font-size:12px;margin-bottom:0;">
                            <i class="fas fa-cog"></i>
                        </div>
                        Pengaturan Sistem
                    </div>
                    <ul class="feature-list">
                        <li><i class="fas fa-circle" style="color:var(--red);font-size:6px;"></i> Konfigurasi auto-isolir & grace period</li>
                        <li><i class="fas fa-circle" style="color:var(--red);font-size:6px;"></i> Info database & arsitektur sistem</li>
                        <li><i class="fas fa-circle" style="color:var(--red);font-size:6px;"></i> Backup database</li>
                        <li><i class="fas fa-circle" style="color:var(--red);font-size:6px;"></i> Activity log & audit trail</li>
                        <li><i class="fas fa-circle" style="color:var(--red);font-size:6px;"></i> Kelola riwayat isolir</li>
                    </ul>
                </div>

            </div>
        </div>

        {{-- ────────────────────────────────────────
             TAB: KASIR
        ──────────────────────────────────────── --}}
        <div x-show="tab === 'kasir'" x-transition>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

                <div class="feature-section">
                    <div class="feature-section-title">
                        <div class="stat-icon green" style="width:28px;height:28px;font-size:12px;margin-bottom:0;">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        Dashboard
                    </div>
                    <ul class="feature-list">
                        <li><i class="fas fa-circle" style="color:var(--green);font-size:6px;"></i> Melihat statistik ringkas</li>
                        <li><i class="fas fa-circle" style="color:var(--green);font-size:6px;"></i> Chart revenue & distribusi paket</li>
                        <li><i class="fas fa-circle" style="color:var(--green);font-size:6px;"></i> Pelanggan overdue & akan jatuh tempo</li>
                    </ul>
                </div>

                <div class="feature-section">
                    <div class="feature-section-title">
                        <div class="stat-icon green" style="width:28px;height:28px;font-size:12px;margin-bottom:0;">
                            <i class="fas fa-id-card"></i>
                        </div>
                        Pelanggan
                    </div>
                    <ul class="feature-list">
                        <li><i class="fas fa-circle" style="color:var(--green);font-size:6px;"></i> Tambah pelanggan baru</li>
                        <li><i class="fas fa-circle" style="color:var(--green);font-size:6px;"></i> Edit data pelanggan</li>
                        <li><i class="fas fa-circle" style="color:var(--green);font-size:6px;"></i> Hapus pelanggan</li>
                        <li><i class="fas fa-circle" style="color:var(--green);font-size:6px;"></i> Isolir pelanggan (suspend)</li>
                        <li><i class="fas fa-circle" style="color:var(--green);font-size:6px;"></i> Aktifkan kembali pelanggan</li>
                    </ul>
                </div>

                <div class="feature-section">
                    <div class="feature-section-title">
                        <div class="stat-icon amber" style="width:28px;height:28px;font-size:12px;margin-bottom:0;">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        Invoice
                    </div>
                    <ul class="feature-list">
                        <li><i class="fas fa-circle" style="color:var(--amber);font-size:6px;"></i> Buat invoice baru</li>
                        <li><i class="fas fa-circle" style="color:var(--amber);font-size:6px;"></i> Edit invoice</li>
                        <li><i class="fas fa-circle" style="color:var(--amber);font-size:6px;"></i> Export PDF invoice</li>
                        <li><i class="fas fa-circle" style="color:var(--amber);font-size:6px;"></i> Tandai invoice lunas</li>
                    </ul>
                </div>

                <div class="feature-section">
                    <div class="feature-section-title">
                        <div class="stat-icon green" style="width:28px;height:28px;font-size:12px;margin-bottom:0;">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        Pembayaran & Notifikasi
                    </div>
                    <ul class="feature-list">
                        <li><i class="fas fa-circle" style="color:var(--green);font-size:6px;"></i> Catat pembayaran manual (transfer/cash)</li>
                        <li><i class="fas fa-circle" style="color:var(--green);font-size:6px;"></i> Lihat riwayat pembayaran</li>
                        <li><i class="fas fa-circle" style="color:var(--green);font-size:6px;"></i> Terima notifikasi sistem</li>
                        <li><i class="fas fa-circle" style="color:var(--green);font-size:6px;"></i> Alert pelanggan jatuh tempo</li>
                    </ul>
                </div>

                <div class="feature-section" style="grid-column:1/-1;">
                    <div style="background:var(--amber-d);border:1px solid rgba(245,158,11,0.25);border-radius:var(--radius);padding:14px;display:flex;gap:12px;align-items:flex-start;">
                        <i class="fas fa-exclamation-triangle" style="color:var(--amber);margin-top:2px;"></i>
                        <div>
                            <div style="font-size:13px;font-weight:600;color:#fcd34d;margin-bottom:4px;">Akses Terbatas untuk Kasir</div>
                            <div style="font-size:12px;color:var(--text-3);">Kasir tidak dapat mengakses menu Jaringan, Pengguna Sistem, Pengaturan, dan Activity Log. Untuk kebutuhan tersebut, hubungi Admin.</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ────────────────────────────────────────
             TAB: TEKNISI
        ──────────────────────────────────────── --}}
        <div x-show="tab === 'teknisi'" x-transition>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

                <div class="feature-section">
                    <div class="feature-section-title">
                        <div class="stat-icon amber" style="width:28px;height:28px;font-size:12px;margin-bottom:0;">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        Dashboard
                    </div>
                    <ul class="feature-list">
                        <li><i class="fas fa-circle" style="color:var(--amber);font-size:6px;"></i> Melihat statistik jaringan</li>
                        <li><i class="fas fa-circle" style="color:var(--amber);font-size:6px;"></i> Status pelanggan online/offline</li>
                    </ul>
                </div>

                <div class="feature-section">
                    <div class="feature-section-title">
                        <div class="stat-icon amber" style="width:28px;height:28px;font-size:12px;margin-bottom:0;">
                            <i class="fas fa-id-card"></i>
                        </div>
                        Pelanggan (Read-only)
                    </div>
                    <ul class="feature-list">
                        <li><i class="fas fa-circle" style="color:var(--amber);font-size:6px;"></i> Lihat detail pelanggan</li>
                        <li><i class="fas fa-circle" style="color:var(--amber);font-size:6px;"></i> Lihat PPPoE username & NAS</li>
                        <li><i class="fas fa-times" style="color:var(--red);font-size:10px;"></i> Tidak bisa edit/hapus</li>
                    </ul>
                </div>

                <div class="feature-section">
                    <div class="feature-section-title">
                        <div class="stat-icon sky" style="width:28px;height:28px;font-size:12px;margin-bottom:0;">
                            <i class="fas fa-network-wired"></i>
                        </div>
                        Monitor Jaringan
                    </div>
                    <ul class="feature-list">
                        <li><i class="fas fa-circle" style="color:var(--sky);font-size:6px;"></i> Monitor RADIUS sessions aktif</li>
                        <li><i class="fas fa-circle" style="color:var(--sky);font-size:6px;"></i> Lihat daftar & status OLT</li>
                        <li><i class="fas fa-circle" style="color:var(--sky);font-size:6px;"></i> Lihat ONU per port OLT</li>
                        <li><i class="fas fa-circle" style="color:var(--sky);font-size:6px;"></i> Lihat NAS yang terdaftar</li>
                        <li><i class="fas fa-circle" style="color:var(--sky);font-size:6px;"></i> Lihat signal & status ONU</li>
                    </ul>
                </div>

                <div class="feature-section">
                    <div class="feature-section-title">
                        <div class="stat-icon amber" style="width:28px;height:28px;font-size:12px;margin-bottom:0;">
                            <i class="fas fa-broadcast-tower"></i>
                        </div>
                        ONU Reboot & Notifikasi
                    </div>
                    <ul class="feature-list">
                        <li><i class="fas fa-circle" style="color:var(--amber);font-size:6px;"></i> Trigger reboot ONU via tombol</li>
                        <li><i class="fas fa-circle" style="color:var(--amber);font-size:6px;"></i> Lihat log reboot ONU</li>
                        <li><i class="fas fa-circle" style="color:var(--amber);font-size:6px;"></i> Terima notifikasi sistem</li>
                        <li><i class="fas fa-circle" style="color:var(--amber);font-size:6px;"></i> Alert jaringan bermasalah</li>
                    </ul>
                </div>

                <div class="feature-section" style="grid-column:1/-1;">
                    <div style="background:var(--red-d);border:1px solid rgba(239,68,68,0.25);border-radius:var(--radius);padding:14px;display:flex;gap:12px;align-items:flex-start;">
                        <i class="fas fa-lock" style="color:var(--red);margin-top:2px;"></i>
                        <div>
                            <div style="font-size:13px;font-weight:600;color:#fca5a5;margin-bottom:4px;">Akses Terbatas untuk Teknisi</div>
                            <div style="font-size:12px;color:var(--text-3);">Teknisi tidak dapat mengakses Invoice, Pembayaran, Kelola Pengguna, dan Pengaturan Sistem. Fokus pada monitoring dan maintenance jaringan.</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>{{-- /card-body --}}
</div>{{-- /card --}}

{{-- ══════════════════════════════════════════════
     ALUR KERJA (2 flowcharts)
══════════════════════════════════════════════ --}}
<div class="grid-2" style="gap:20px;margin-bottom:20px;">

    {{-- Flowchart 1: Aktivasi Pelanggan Baru --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">
                    <i class="fas fa-user-plus" style="color:var(--green);margin-right:6px;"></i>
                    Alur Aktivasi Pelanggan Baru
                </div>
                <div class="card-subtitle">Proses dari pendaftaran hingga pelanggan aktif</div>
            </div>
        </div>
        <div class="card-body">
            <div style="display:flex;flex-direction:column;gap:10px;">

                <div class="flow-step">
                    <div class="flow-num" style="background:var(--indigo-dim);border-color:rgba(99,102,241,0.4);color:#a5b4fc;">1</div>
                    <div class="flow-body">
                        <div class="flow-body-title">Tambah Pelanggan</div>
                        <div class="flow-body-desc">Kasir/Admin isi form: nama, alamat, PPPoE username & password</div>
                    </div>
                </div>

                <div class="flow-step">
                    <div class="flow-num" style="background:var(--sky-d);border-color:rgba(14,165,233,0.4);color:#7dd3fc;">2</div>
                    <div class="flow-body">
                        <div class="flow-body-title">Pilih Paket & NAS</div>
                        <div class="flow-body-desc">Pilih paket layanan dan NAS (router) yang akan menangani pelanggan</div>
                    </div>
                </div>

                <div class="flow-step">
                    <div class="flow-num" style="background:var(--amber-d);border-color:rgba(245,158,11,0.4);color:#fcd34d;">3</div>
                    <div class="flow-body">
                        <div class="flow-body-title">Sistem Menyimpan Data</div>
                        <div class="flow-body-desc">Data pelanggan & PPPoE credentials disimpan ke database</div>
                    </div>
                </div>

                <div class="flow-step">
                    <div class="flow-num" style="background:var(--indigo-dim);border-color:rgba(99,102,241,0.4);color:#a5b4fc;">4</div>
                    <div class="flow-body">
                        <div class="flow-body-title">Buat Invoice Pertama</div>
                        <div class="flow-body-desc">Kasir membuat invoice bulan pertama berdasarkan paket yang dipilih</div>
                    </div>
                </div>

                <div class="flow-step">
                    <div class="flow-num" style="background:var(--amber-d);border-color:rgba(245,158,11,0.4);color:#fcd34d;">5</div>
                    <div class="flow-body">
                        <div class="flow-body-title">Catat Pembayaran</div>
                        <div class="flow-body-desc">Input metode bayar (transfer/cash) dan nominal yang diterima</div>
                    </div>
                </div>

                <div class="flow-step" style="margin-bottom:0;">
                    <div class="flow-num" style="background:var(--green-d);border-color:rgba(16,185,129,0.4);color:#6ee7b7;">6</div>
                    <div class="flow-body" style="border-color:rgba(16,185,129,0.3);background:rgba(16,185,129,0.05);">
                        <div class="flow-body-title" style="color:#6ee7b7;">
                            <i class="fas fa-check-circle" style="margin-right:5px;"></i>Pelanggan Aktif
                        </div>
                        <div class="flow-body-desc">Status berubah menjadi Aktif, pelanggan dapat menggunakan layanan internet</div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Flowchart 2: Auto-Isolir --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">
                    <i class="fas fa-clock" style="color:var(--amber);margin-right:6px;"></i>
                    Alur Auto-Isolir
                </div>
                <div class="card-subtitle">Proses isolir otomatis pelanggan yang menunggak</div>
            </div>
        </div>
        <div class="card-body">
            <div style="display:flex;flex-direction:column;gap:10px;">

                <div class="flow-step">
                    <div class="flow-num" style="background:var(--sky-d);border-color:rgba(14,165,233,0.4);color:#7dd3fc;">1</div>
                    <div class="flow-body">
                        <div class="flow-body-title">Scheduler Berjalan</div>
                        <div class="flow-body-desc">Job scheduler otomatis dijalankan setiap hari pukul 00:01 WIB</div>
                    </div>
                </div>

                <div class="flow-step">
                    <div class="flow-num" style="background:var(--indigo-dim);border-color:rgba(99,102,241,0.4);color:#a5b4fc;">2</div>
                    <div class="flow-body">
                        <div class="flow-body-title">Cek Invoice Overdue</div>
                        <div class="flow-body-desc">Sistem mengecek invoice yang belum lunas + melewati grace period</div>
                    </div>
                </div>

                <div class="flow-step">
                    <div class="flow-num" style="background:var(--red-d);border-color:rgba(239,68,68,0.4);color:#fca5a5;">3</div>
                    <div class="flow-body" style="border-color:rgba(239,68,68,0.2);background:rgba(239,68,68,0.04);">
                        <div class="flow-body-title" style="color:#fca5a5;">Isolir Otomatis</div>
                        <div class="flow-body-desc">Jika jatuh tempo + grace period terlampaui → status diubah ke "Suspend"</div>
                    </div>
                </div>

                <div class="flow-step">
                    <div class="flow-num" style="background:var(--amber-d);border-color:rgba(245,158,11,0.4);color:#fcd34d;">4</div>
                    <div class="flow-body">
                        <div class="flow-body-title">Catat di Log Isolir</div>
                        <div class="flow-body-desc">Event isolir dicatat di tabel isolir_log & activity_log untuk audit</div>
                    </div>
                </div>

                <div class="flow-step" style="margin-bottom:0;">
                    <div class="flow-num" style="background:var(--green-d);border-color:rgba(16,185,129,0.4);color:#6ee7b7;">5</div>
                    <div class="flow-body" style="border-color:rgba(16,185,129,0.3);background:rgba(16,185,129,0.05);">
                        <div class="flow-body-title" style="color:#6ee7b7;">
                            <i class="fas fa-hand-pointer" style="margin-right:5px;"></i>Aktivasi Manual
                        </div>
                        <div class="flow-body-desc">Admin/Kasir dapat mengaktifkan kembali pelanggan kapan saja dari halaman Pelanggan</div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════
     FAQ ACCORDION
══════════════════════════════════════════════ --}}
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">
                <i class="fas fa-question-circle" style="color:var(--sky);margin-right:6px;"></i>
                Pertanyaan yang Sering Ditanyakan
            </div>
            <div class="card-subtitle">FAQ seputar penggunaan sistem NetCORE</div>
        </div>
    </div>
    <div class="card-body" style="display:flex;flex-direction:column;gap:0;">

        @php
        $faqs = [
            [
                'q' => 'Apa itu grace period dan bagaimana cara mengaturnya?',
                'a' => 'Grace period adalah jumlah hari toleransi setelah tanggal jatuh tempo invoice sebelum sistem melakukan auto-isolir. Misalnya jika grace period = 3 hari dan invoice jatuh tempo tanggal 1, pelanggan baru di-isolir pada tanggal 4. Grace period dapat diatur oleh Admin di menu Pengaturan → Konfigurasi Auto-Isolir.',
                'icon' => 'fas fa-clock',
                'color' => 'var(--amber)',
            ],
            [
                'q' => 'Bagaimana jika proses auto-isolir gagal atau error?',
                'a' => 'Jika auto-isolir otomatis gagal (misalnya koneksi RADIUS terputus), Admin atau Kasir tetap dapat melakukan isolir manual dari halaman detail pelanggan. Klik tombol "Isolir" pada kartu aksi pelanggan. Log error juga dapat dilihat di menu Sistem → Activity Log.',
                'icon' => 'fas fa-exclamation-triangle',
                'color' => 'var(--red)',
            ],
            [
                'q' => 'Apakah data pelanggan aman tersimpan di sistem?',
                'a' => 'Ya, keamanan data dijaga dengan beberapa lapisan: (1) Password pengguna sistem di-hash menggunakan bcrypt, (2) Password PPPoE pelanggan dienkripsi, (3) Akses fitur dibatasi per role (Admin/Kasir/Teknisi), (4) Semua aksi dicatat di Activity Log untuk audit trail, (5) Sistem menggunakan CSRF token untuk mencegah serangan.',
                'icon' => 'fas fa-shield-alt',
                'color' => 'var(--green)',
            ],
            [
                'q' => 'Bagaimana cara export invoice ke format PDF?',
                'a' => 'Buka halaman detail invoice dengan mengklik nomor invoice di daftar Invoice. Di bagian kanan atas halaman detail, klik tombol "Export PDF" (ikon unduh). File PDF akan langsung didownload ke perangkat Anda. Pastikan browser tidak memblokir pop-up untuk domain ini.',
                'icon' => 'fas fa-file-pdf',
                'color' => 'var(--red)',
            ],
            [
                'q' => 'Apa perbedaan status Isolir dan Nonaktif pada pelanggan?',
                'a' => 'Isolir (Suspend) bersifat sementara — pelanggan yang diisolir dapat diaktifkan kembali kapan saja oleh Admin/Kasir, biasanya setelah pelanggan melunasi tagihan. Nonaktif bersifat lebih permanen, digunakan ketika pelanggan mengakhiri langganan. Keduanya memutus akses internet, namun data pelanggan tetap tersimpan di sistem.',
                'icon' => 'fas fa-question',
                'color' => 'var(--indigo)',
            ],
        ];
        @endphp

        @foreach($faqs as $i => $faq)
        <div class="faq-item" x-data="{ open: false }">
            <div class="faq-question" @click="open = !open">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:var(--bg-base);flex-shrink:0;">
                        <i class="{{ $faq['icon'] }}" style="font-size:11px;color:{{ $faq['color'] }};"></i>
                    </div>
                    <span>{{ $faq['q'] }}</span>
                </div>
                <i class="fas fa-chevron-down chevron" :style="open ? 'transform:rotate(180deg)' : ''"></i>
            </div>
            <div class="faq-answer" x-show="open" x-collapse>
                <div style="display:flex;gap:10px;align-items:flex-start;">
                    <i class="fas fa-info-circle" style="color:var(--sky);margin-top:2px;flex-shrink:0;"></i>
                    <span>{{ $faq['a'] }}</span>
                </div>
            </div>
        </div>
        @endforeach

    </div>
</div>

@endsection
