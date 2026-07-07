@extends('layouts.app')
@section('title', 'Notifikasi')
@section('page-title', 'Notifikasi')
@section('breadcrumb', 'Sistem / Notifikasi')

@section('content')

<style>
.notif-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 20px;
}

.notif-item {
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    display: flex;
    align-items: flex-start;
    gap: 0;
    overflow: hidden;
    transition: border-color var(--transition), background var(--transition);
}

.notif-item:hover { border-color: var(--border-light); }

.notif-item.unread {
    background: rgba(255,255,255,0.025);
    border-color: var(--border-light);
}

.notif-border {
    width: 4px;
    align-self: stretch;
    flex-shrink: 0;
}

.notif-border.type-danger  { background: var(--red); }
.notif-border.type-warning { background: var(--amber); }
.notif-border.type-info    { background: var(--sky); }
.notif-border.type-success { background: var(--green); }

.notif-icon-wrap {
    padding: 16px 14px 16px 16px;
    flex-shrink: 0;
}

.notif-icon {
    width: 40px; height: 40px;
    border-radius: var(--radius);
    display: flex; align-items: center; justify-content: center;
    font-size: 17px;
}

.notif-icon.type-danger  { background: var(--red-d);   color: #fca5a5; }
.notif-icon.type-warning { background: var(--amber-d); color: #fcd34d; }
.notif-icon.type-info    { background: var(--sky-d);   color: #7dd3fc; }
.notif-icon.type-success { background: var(--green-d); color: #6ee7b7; }

.notif-body {
    flex: 1;
    padding: 14px 16px 14px 0;
    min-width: 0;
}

.notif-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-1);
    margin-bottom: 3px;
}

.notif-desc {
    font-size: 12px;
    color: var(--text-3);
    line-height: 1.5;
    word-break: break-word;
}

.notif-time {
    font-size: 11px;
    color: var(--text-4);
    font-family: 'JetBrains Mono', monospace;
    margin-top: 6px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.notif-actions {
    padding: 14px 16px;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    justify-content: space-between;
    gap: 8px;
    flex-shrink: 0;
}

.unread-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: var(--indigo);
    box-shadow: 0 0 6px rgba(99,102,241,0.5);
    flex-shrink: 0;
}

/* Filter bar */
.filter-bar {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 16px;
    padding: 12px 16px;
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
}

.filter-group {
    display: flex;
    align-items: center;
    gap: 6px;
}

.filter-label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-4);
    white-space: nowrap;
}

.filter-select {
    background: var(--bg-input);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--text-1);
    font-size: 12px;
    padding: 5px 8px;
    cursor: pointer;
    outline: none;
    transition: border-color var(--transition);
}

.filter-select:focus { border-color: var(--indigo); }
.filter-select option { background: var(--bg-surface); }
</style>

<div class="page-header">
    <div class="page-header-title">
        <h1>Notifikasi</h1>
        <p>Pesan dan peringatan sistem untuk tindakan yang diperlukan</p>
    </div>
    <div class="page-header-actions">
        @php $unreadCount = isset($notifikasis) ? $notifikasis->where('read_at', null)->count() : 0; @endphp
        @if($unreadCount > 0)
        <form method="POST" action="{{ route('notifikasi.bacaSemua') }}">
            @csrf
            <button type="submit" class="btn btn-ghost">
                <i class="fas fa-check-double"></i> Tandai Semua Dibaca
                <span style="background:var(--indigo-dim); color:#a5b4fc; font-size:10px; padding:1px 7px; border-radius:100px; font-weight:700; margin-left:2px;">
                    {{ $unreadCount }}
                </span>
            </button>
        </form>
        @else
        <button type="button" class="btn btn-ghost" disabled style="opacity:0.4; cursor:not-allowed;">
            <i class="fas fa-check-double"></i> Tandai Semua Dibaca
        </button>
        @endif
    </div>
</div>

{{-- Filter Bar --}}
<div class="filter-bar">
    <form method="GET" action="{{ route('notifikasi.index') }}" style="display:flex; gap:12px; align-items:center; flex-wrap:wrap; width:100%;">
        <div class="filter-group">
            <span class="filter-label">Tipe:</span>
            <select name="type" class="filter-select" onchange="this.form.submit()">
                <option value="">Semua Tipe</option>
                <option value="danger"  {{ request('type') === 'danger'  ? 'selected' : '' }}>Bahaya</option>
                <option value="warning" {{ request('type') === 'warning' ? 'selected' : '' }}>Peringatan</option>
                <option value="info"    {{ request('type') === 'info'    ? 'selected' : '' }}>Informasi</option>
                <option value="success" {{ request('type') === 'success' ? 'selected' : '' }}>Sukses</option>
            </select>
        </div>

        <div class="filter-group">
            <span class="filter-label">Status:</span>
            <select name="status" class="filter-select" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="unread" {{ request('status') === 'unread' ? 'selected' : '' }}>Belum Dibaca</option>
                <option value="read"   {{ request('status') === 'read'   ? 'selected' : '' }}>Sudah Dibaca</option>
            </select>
        </div>

        @if(request('type') || request('status'))
        <a href="{{ route('notifikasi.index') }}" class="btn btn-ghost btn-xs" style="margin-left:auto;">
            <i class="fas fa-times"></i> Reset Filter
        </a>
        @endif
    </form>
</div>

{{-- Notification List --}}
@forelse($notifikasis ?? [] as $notif)
@php
    $type   = $notif->type ?? 'info';
    $isRead = !is_null($notif->read_at);

    $icons = [
        'danger'  => 'fas fa-triangle-exclamation',
        'warning' => 'fas fa-bell',
        'info'    => 'fas fa-circle-info',
        'success' => 'fas fa-circle-check',
    ];
    $icon = $icons[$type] ?? 'fas fa-circle-info';
@endphp
<div class="notif-item {{ !$isRead ? 'unread' : '' }}">
    <div class="notif-border type-{{ $type }}"></div>

    <div class="notif-icon-wrap">
        <div class="notif-icon type-{{ $type }}">
            <i class="{{ $icon }}"></i>
        </div>
    </div>

    <div class="notif-body">
        <div class="notif-title">
            {{ $notif->title ?? $notif->judul ?? 'Notifikasi' }}
            @if(!$isRead)
                <span style="display:inline-block; width:7px; height:7px; border-radius:50%; background:var(--indigo); margin-left:6px; vertical-align:middle; box-shadow:0 0 5px rgba(99,102,241,0.6);"></span>
            @endif
        </div>
        <div class="notif-desc">{{ $notif->deskripsi ?? $notif->body ?? $notif->message ?? '' }}</div>
        <div class="notif-time">
            <i class="fas fa-clock"></i>
            {{ \Carbon\Carbon::parse($notif->created_at)->diffForHumans() }}
            <span style="color:var(--border-light);">·</span>
            <span>{{ \Carbon\Carbon::parse($notif->created_at)->format('d/m/Y H:i') }}</span>
        </div>
    </div>

    <div class="notif-actions">
        @if(!$isRead)
            <form method="POST" action="{{ route('notifikasi.baca', $notif->id) }}">
                @csrf
                <button type="submit" class="btn btn-ghost btn-xs" title="Tandai sudah dibaca">
                    <i class="fas fa-check"></i> Baca
                </button>
            </form>
        @else
            <span style="font-size:10px; color:var(--text-4); font-family:'JetBrains Mono',monospace; padding:4px 0;">
                <i class="fas fa-check-double"></i> Dibaca
            </span>
        @endif
        @if(!$isRead)
            <div class="unread-dot"></div>
        @endif
    </div>
</div>
@empty
<div class="card">
    <div class="card-body" style="text-align:center; padding:60px 20px;">
        <i class="fas fa-bell-slash" style="font-size:42px; color:var(--border-light); display:block; margin-bottom:16px;"></i>
        <div style="font-size:15px; font-weight:600; color:var(--text-3); margin-bottom:6px;">
            @if(request('type') || request('status'))
                Tidak Ada Notifikasi yang Cocok
            @else
                Tidak Ada Notifikasi
            @endif
        </div>
        <div style="font-size:13px; color:var(--text-4); max-width:340px; margin:0 auto;">
            @if(request('type') || request('status'))
                Tidak ada notifikasi yang sesuai dengan filter yang dipilih.
                <br><a href="{{ route('notifikasi.index') }}" style="color:var(--indigo); text-decoration:none;">Reset filter</a>
            @else
                Semua notifikasi sudah dibaca. Sistem akan menampilkan peringatan baru di sini.
            @endif
        </div>
    </div>
</div>
@endforelse

{{-- Pagination --}}
@if(isset($notifikasis) && method_exists($notifikasis, 'hasPages') && $notifikasis->hasPages())
<div style="display:flex; justify-content:space-between; align-items:center; margin-top:12px; padding:12px 0; flex-wrap:wrap; gap:8px;">
    <span class="mono-mute">
        Menampilkan {{ $notifikasis->firstItem() }}–{{ $notifikasis->lastItem() }} dari {{ $notifikasis->total() }} notifikasi
    </span>
    <div style="display:flex; gap:4px; align-items:center;">
        @if($notifikasis->onFirstPage())
            <span class="btn btn-ghost btn-xs" style="opacity:0.4; cursor:not-allowed;"><i class="fas fa-chevron-left"></i></span>
        @else
            <a href="{{ $notifikasis->previousPageUrl() . (request()->getQueryString() ? '&' . request()->getQueryString() : '') }}" class="btn btn-ghost btn-xs">
                <i class="fas fa-chevron-left"></i>
            </a>
        @endif

        @foreach($notifikasis->getUrlRange(max(1, $notifikasis->currentPage()-2), min($notifikasis->lastPage(), $notifikasis->currentPage()+2)) as $page => $url)
            <a href="{{ $url }}" class="btn btn-xs {{ $page == $notifikasis->currentPage() ? 'btn-primary' : 'btn-ghost' }}">{{ $page }}</a>
        @endforeach

        @if($notifikasis->hasMorePages())
            <a href="{{ $notifikasis->nextPageUrl() . (request()->getQueryString() ? '&' . request()->getQueryString() : '') }}" class="btn btn-ghost btn-xs">
                <i class="fas fa-chevron-right"></i>
            </a>
        @else
            <span class="btn btn-ghost btn-xs" style="opacity:0.4; cursor:not-allowed;"><i class="fas fa-chevron-right"></i></span>
        @endif
    </div>
</div>
@endif

@endsection
