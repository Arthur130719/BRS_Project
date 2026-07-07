@extends('layouts.app')
@section('title', 'RADIUS Sessions')
@section('page-title', 'RADIUS Sessions')
@section('breadcrumb', 'Network / RADIUS Sessions')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>RADIUS Sessions</h1>
        <p>Sesi aktif pelanggan melalui RADIUS server</p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('radius.index') }}" class="btn btn-ghost">
            <i class="fas fa-rotate-right"></i> Refresh
        </a>
    </div>
</div>

{{-- Stats --}}
<div class="stats-grid">
    <div class="stat-card indigo">
        <div class="stat-icon indigo"><i class="fas fa-wifi"></i></div>
        <div class="stat-value">{{ $totalOnline ?? 0 }}</div>
        <div class="stat-label">Total Online</div>
        <div class="stat-sub up"><i class="fas fa-circle"></i> Sesi Aktif</div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon green"><i class="fas fa-arrow-down"></i></div>
        <div class="stat-value">{{ $totalDownload ?? '0 B' }}</div>
        <div class="stat-label">Total Download</div>
        <div class="stat-sub mute"><i class="fas fa-database"></i> Akumulasi</div>
    </div>
    <div class="stat-card sky">
        <div class="stat-icon sky"><i class="fas fa-arrow-up"></i></div>
        <div class="stat-value">{{ $totalUpload ?? '0 B' }}</div>
        <div class="stat-label">Total Upload</div>
        <div class="stat-sub mute"><i class="fas fa-database"></i> Akumulasi</div>
    </div>
</div>

{{-- Table Card --}}
<div class="card">
    <div class="table-toolbar">
        <form method="GET" action="{{ route('radius.index') }}" class="toolbar-search">
            <i class="fas fa-search"></i>
            <input type="text" name="search" placeholder="Cari username / IP..." value="{{ request('search') }}">
        </form>
        <div class="toolbar-right">
            <span class="mono-mute" style="font-size:11px;">
                {{ $sessions->total() ?? 0 }} sesi ditemukan
            </span>
        </div>
    </div>

    <div class="card-body-flush">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>IP Address</th>
                        <th>NAS</th>
                        <th>Uptime</th>
                        <th>Download</th>
                        <th>Upload</th>
                        <th>Rate</th>
                        <th>MAC Address</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $session)
                    <tr>
                        <td>
                            <span class="mono">{{ $session->username }}</span>
                        </td>
                        <td>
                            <span class="mono">{{ $session->framedipaddress ?? '-' }}</span>
                        </td>
                        <td>
                            <span style="font-size:12px; color:var(--text-2);">{{ $session->nasipaddress ?? '-' }}</span>
                        </td>
                        <td>
                            <span class="mono-mute">{{ $session->acctsessiontime_formatted ?? ($session->acctsessiontime ? gmdate('H:i:s', $session->acctsessiontime) : '-') }}</span>
                        </td>
                        <td>
                            <span class="mono" style="color:var(--green);">
                                {{ $session->acctinputoctets_formatted ?? (isset($session->acctinputoctets) ? number_format($session->acctinputoctets / 1048576, 2) . ' MB' : '-') }}
                            </span>
                        </td>
                        <td>
                            <span class="mono" style="color:var(--sky);">
                                {{ $session->acctoutputoctets_formatted ?? (isset($session->acctoutputoctets) ? number_format($session->acctoutputoctets / 1048576, 2) . ' MB' : '-') }}
                            </span>
                        </td>
                        <td>
                            <span class="mono-mute">{{ $session->rate ?? '-' }}</span>
                        </td>
                        <td>
                            <span class="mono-mute" style="font-size:11px;">{{ $session->callingstationid ?? '-' }}</span>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('radius.disconnect', $session->radacctid ?? $session->id) }}"
                                  onsubmit="return confirm('Disconnect sesi {{ $session->username }}? Koneksi pelanggan akan diputus.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-xs">
                                    <i class="fas fa-plug-circle-xmark"></i> Disconnect
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9">
                            <div style="text-align:center; padding:48px 20px; color:var(--text-4);">
                                <i class="fas fa-wifi" style="font-size:36px; margin-bottom:12px; display:block; color:var(--border-light);"></i>
                                <div style="font-weight:600; color:var(--text-3); margin-bottom:4px;">Tidak Ada Sesi Aktif</div>
                                <div style="font-size:12px;">Belum ada pelanggan yang terhubung melalui RADIUS saat ini.</div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(isset($sessions) && $sessions->hasPages())
    <div style="padding: 12px 16px; border-top: 1px solid var(--border); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
        <span class="mono-mute">
            Menampilkan {{ $sessions->firstItem() }}–{{ $sessions->lastItem() }} dari {{ $sessions->total() }} sesi
        </span>
        <div style="display:flex; gap:4px; align-items:center;">
            @if($sessions->onFirstPage())
                <span class="btn btn-ghost btn-xs" style="opacity:0.4; cursor:not-allowed;">
                    <i class="fas fa-chevron-left"></i>
                </span>
            @else
                <a href="{{ $sessions->previousPageUrl() }}" class="btn btn-ghost btn-xs">
                    <i class="fas fa-chevron-left"></i>
                </a>
            @endif

            @foreach($sessions->getUrlRange(max(1, $sessions->currentPage()-2), min($sessions->lastPage(), $sessions->currentPage()+2)) as $page => $url)
                <a href="{{ $url }}"
                   class="btn btn-xs {{ $page == $sessions->currentPage() ? 'btn-primary' : 'btn-ghost' }}">
                    {{ $page }}
                </a>
            @endforeach

            @if($sessions->hasMorePages())
                <a href="{{ $sessions->nextPageUrl() }}" class="btn btn-ghost btn-xs">
                    <i class="fas fa-chevron-right"></i>
                </a>
            @else
                <span class="btn btn-ghost btn-xs" style="opacity:0.4; cursor:not-allowed;">
                    <i class="fas fa-chevron-right"></i>
                </span>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection
