@extends('layouts.app')

@section('title', 'Activity Log')
@section('page-title', 'Activity Log')
@section('breadcrumb', 'Sistem / Activity Log')

@section('content')
{{-- ══════════════════════════════════════════════
     PAGE HEADER
══════════════════════════════════════════════ --}}
<div class="page-header">
    <div class="page-header-title">
        <h1><i class="fas fa-scroll" style="color:var(--indigo);margin-right:8px;"></i>Riwayat Aktivitas</h1>
        <p>Audit trail semua aksi pengguna dalam sistem</p>
    </div>
    <div class="page-header-actions">
        <span class="mono-mute" style="font-size:11px;align-self:center;">
            <i class="fas fa-clock" style="margin-right:4px;"></i>
            {{ now()->format('d M Y, H:i') }} WIB
        </span>
    </div>
</div>

{{-- ══════════════════════════════════════════════
     STATS GRID
══════════════════════════════════════════════ --}}
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">

    {{-- Total Log --}}
    <div class="stat-card indigo">
        <div class="stat-icon indigo">
            <i class="fas fa-list-ul"></i>
        </div>
        <div class="stat-value">{{ number_format($stats['total']) }}</div>
        <div class="stat-label">Total Log</div>
        <div class="stat-sub mute">
            <i class="fas fa-database"></i> semua waktu
        </div>
    </div>

    {{-- Hari Ini --}}
    <div class="stat-card green">
        <div class="stat-icon green">
            <i class="fas fa-calendar-day"></i>
        </div>
        <div class="stat-value">{{ number_format($stats['today']) }}</div>
        <div class="stat-label">Hari Ini</div>
        <div class="stat-sub up">
            <i class="fas fa-arrow-up"></i> aktivitas hari ini
        </div>
    </div>

    {{-- Minggu Ini --}}
    <div class="stat-card sky">
        <div class="stat-icon sky">
            <i class="fas fa-calendar-week"></i>
        </div>
        <div class="stat-value">{{ number_format($stats['this_week']) }}</div>
        <div class="stat-label">Minggu Ini</div>
        <div class="stat-sub mute">
            <i class="fas fa-chart-line"></i> 7 hari terakhir
        </div>
    </div>

    {{-- Aksi Hapus --}}
    <div class="stat-card red">
        <div class="stat-icon red">
            <i class="fas fa-trash-alt"></i>
        </div>
        <div class="stat-value">{{ number_format($stats['delete_actions']) }}</div>
        <div class="stat-label">Aksi Hapus</div>
        <div class="stat-sub down">
            <i class="fas fa-exclamation-triangle"></i> perlu perhatian
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════
     FILTER + TABLE CARD
══════════════════════════════════════════════ --}}
<div class="card">

    {{-- Filter Toolbar --}}
    <form method="GET" action="{{ route('activity_log.index') }}" id="filterForm">
        <div class="table-toolbar" style="flex-wrap:wrap;gap:10px;">

            {{-- Search --}}
            <div class="toolbar-search" style="width:220px;">
                <i class="fas fa-search"></i>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Cari deskripsi...">
            </div>

            <div class="toolbar-right" style="flex-wrap:wrap;gap:8px;">

                {{-- Module Filter --}}
                <select name="module" class="form-control" style="width:140px;height:32px;padding:4px 8px;font-size:12px;"
                        onchange="document.getElementById('filterForm').submit()">
                    <option value="">Semua Modul</option>
                    @foreach($modules as $mod)
                        <option value="{{ $mod }}" {{ request('module') === $mod ? 'selected' : '' }}>
                            {{ ucfirst($mod) }}
                        </option>
                    @endforeach
                </select>

                {{-- Action Filter --}}
                <select name="action" class="form-control" style="width:140px;height:32px;padding:4px 8px;font-size:12px;"
                        onchange="document.getElementById('filterForm').submit()">
                    <option value="">Semua Aksi</option>
                    @foreach($actions as $act)
                        <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>
                            {{ ucfirst($act) }}
                        </option>
                    @endforeach
                </select>

                {{-- User Filter --}}
                <select name="user_id" class="form-control" style="width:160px;height:32px;padding:4px 8px;font-size:12px;"
                        onchange="document.getElementById('filterForm').submit()">
                    <option value="">Semua Pengguna</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>

                {{-- Date From --}}
                <div style="display:flex;align-items:center;gap:6px;">
                    <span style="font-size:11px;color:var(--text-3);">Dari</span>
                    <input type="date"
                           name="date_from"
                           value="{{ request('date_from') }}"
                           class="form-control"
                           style="width:140px;height:32px;padding:4px 8px;font-size:12px;"
                           onchange="document.getElementById('filterForm').submit()">
                </div>

                {{-- Date To --}}
                <div style="display:flex;align-items:center;gap:6px;">
                    <span style="font-size:11px;color:var(--text-3);">s/d</span>
                    <input type="date"
                           name="date_to"
                           value="{{ request('date_to') }}"
                           class="form-control"
                           style="width:140px;height:32px;padding:4px 8px;font-size:12px;"
                           onchange="document.getElementById('filterForm').submit()">
                </div>

                {{-- Reset Button --}}
                @if(request('search') || request('module') || request('action') || request('user_id') || request('date_from') || request('date_to'))
                    <a href="{{ route('activity_log.index') }}" class="btn btn-ghost btn-sm">
                        <i class="fas fa-times"></i> Reset
                    </a>
                @endif

                {{-- Submit (search) --}}
                <button type="submit" class="btn btn-ghost btn-sm">
                    <i class="fas fa-filter"></i> Filter
                </button>

            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="card-body-flush">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Pengguna</th>
                        <th>Aksi</th>
                        <th>Modul</th>
                        <th>Deskripsi</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        {{-- Waktu --}}
                        <td>
                            <span class="mono">{{ $log->created_at->format('d M Y') }}</span><br>
                            <span class="mono-mute">{{ $log->created_at->format('H:i:s') }}</span>
                        </td>

                        {{-- Pengguna --}}
                        <td>
                            @if($log->user)
                                <div style="font-size:13px;font-weight:500;color:var(--text-1);">
                                    {{ $log->user->name }}
                                </div>
                                <div style="margin-top:2px;">
                                    @switch($log->user->role)
                                        @case('admin')
                                            <span class="badge badge-auto" style="font-size:10px;">
                                                <i class="fas fa-shield-alt" style="font-size:8px;"></i> Admin
                                            </span>
                                            @break
                                        @case('kasir')
                                            <span class="badge badge-active" style="font-size:10px;">
                                                <i class="fas fa-cash-register" style="font-size:8px;"></i> Kasir
                                            </span>
                                            @break
                                        @case('teknisi')
                                            <span class="badge badge-suspend" style="font-size:10px;">
                                                <i class="fas fa-tools" style="font-size:8px;"></i> Teknisi
                                            </span>
                                            @break
                                        @default
                                            <span class="badge badge-manual" style="font-size:10px;">
                                                {{ ucfirst($log->user->role) }}
                                            </span>
                                    @endswitch
                                </div>
                            @else
                                <span class="mono-mute">Sistem</span>
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td>
                            @switch($log->action)
                                @case('create')
                                    <span class="badge badge-active">
                                        <i class="fas fa-plus-circle" style="font-size:8px;"></i> Create
                                    </span>
                                    @break
                                @case('update')
                                    <span class="badge badge-auto">
                                        <i class="fas fa-edit" style="font-size:8px;"></i> Update
                                    </span>
                                    @break
                                @case('delete')
                                    <span class="badge badge-inactive">
                                        <i class="fas fa-trash" style="font-size:8px;"></i> Delete
                                    </span>
                                    @break
                                @case('login')
                                    <span class="badge badge-manual">
                                        <i class="fas fa-sign-in-alt" style="font-size:8px;"></i> Login
                                    </span>
                                    @break
                                @case('logout')
                                    <span class="badge badge-suspend">
                                        <i class="fas fa-sign-out-alt" style="font-size:8px;"></i> Logout
                                    </span>
                                    @break
                                @case('suspend')
                                    <span class="badge badge-suspend">
                                        <i class="fas fa-ban" style="font-size:8px;"></i> Suspend
                                    </span>
                                    @break
                                @case('aktifkan')
                                    <span class="badge badge-active">
                                        <i class="fas fa-check-circle" style="font-size:8px;"></i> Aktifkan
                                    </span>
                                    @break
                                @case('reboot')
                                    <span class="badge badge-suspend">
                                        <i class="fas fa-redo" style="font-size:8px;"></i> Reboot
                                    </span>
                                    @break
                                @case('export')
                                    <span class="badge badge-auto">
                                        <i class="fas fa-file-export" style="font-size:8px;"></i> Export
                                    </span>
                                    @break
                                @default
                                    <span class="badge badge-manual">
                                        <i class="fas fa-circle" style="font-size:8px;"></i> {{ ucfirst($log->action) }}
                                    </span>
                            @endswitch
                        </td>

                        {{-- Modul --}}
                        <td>
                            <span class="badge badge-active" style="font-size:10px;text-transform:uppercase;letter-spacing:0.3px;">
                                {{ $log->module ?? '—' }}
                            </span>
                        </td>

                        {{-- Deskripsi --}}
                        <td style="max-width:280px;">
                            <span style="font-size:13px;color:var(--text-2);line-height:1.4;">
                                {{ $log->description ?? '—' }}
                            </span>
                        </td>

                        {{-- IP Address --}}
                        <td>
                            <span class="mono-mute">{{ $log->ip_address ?? '—' }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <i class="fas fa-scroll"></i>
                                <h3>Tidak ada log aktivitas</h3>
                                <p>
                                    @if(request()->hasAny(['search','module','action','user_id','date_from','date_to']))
                                        Tidak ada log yang cocok dengan filter yang dipilih.
                                        <a href="{{ route('activity_log.index') }}" style="color:var(--indigo);">Reset filter</a>
                                    @else
                                        Belum ada aktivitas yang tercatat dalam sistem.
                                    @endif
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($logs->hasPages())
            {{ $logs->appends(request()->query())->links() }}
        @else
            <div style="padding:10px 16px;display:flex;justify-content:space-between;align-items:center;border-top:1px solid var(--border);">
                <span class="mono-mute" style="font-size:11px;">
                    Menampilkan {{ $logs->count() }} entri
                </span>
                <span class="mono-mute" style="font-size:11px;">
                    Total: {{ number_format($logs->total()) }} log
                </span>
            </div>
        @endif

    </div>{{-- /card-body-flush --}}
</div>{{-- /card --}}

@endsection
