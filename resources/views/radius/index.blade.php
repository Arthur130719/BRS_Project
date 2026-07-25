@extends('layouts.app')
@section('title', 'Sesi Aktif (PPPoE)')
@section('page-title', 'Sesi Aktif (PPPoE)')
@section('breadcrumb', 'Network / Sesi Aktif')

@section('content')
<div class="page-header">
    <div class="page-header-title">
        <h1>Sesi Aktif (PPPoE)</h1>
        <p>Monitor real-time sesi pelanggan aktif via API MikroTik</p>
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
        <div class="stat-value" id="stat-online">{{ $totalOnline ?? 0 }}</div>
        <div class="stat-label">Total Online</div>
        <div class="stat-sub up"><i class="fas fa-circle"></i> Sesi Aktif</div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon green"><i class="fas fa-arrow-down"></i></div>
        <div class="stat-value" id="stat-dl">{{ $totalDownload ?? '0 bps' }}</div>
        <div class="stat-label">Total Download</div>
        <div class="stat-sub mute"><i class="fas fa-bolt"></i> Live Rate</div>
    </div>
    <div class="stat-card sky">
        <div class="stat-icon sky"><i class="fas fa-arrow-up"></i></div>
        <div class="stat-value" id="stat-ul">{{ $totalUpload ?? '0 bps' }}</div>
        <div class="stat-label">Total Upload</div>
        <div class="stat-sub mute"><i class="fas fa-bolt"></i> Live Rate</div>
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
                <span id="stat-count">{{ $sessions->total() ?? 0 }}</span> sesi ditemukan
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
                <tbody id="session-tbody">
                    @include('radius.partials.table', ['sessions' => $sessions])
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

<script>
    // Fitur Auto Refresh Live Data per 1 detik (1000 ms)
    setInterval(() => {
        // Jangan auto-refresh kalau ada form yang sedang diketik di kolom search (opsional)
        let searchVal = document.querySelector('input[name="search"]').value;
        let url = new URL(window.location.href);
        url.searchParams.set('ajax', '1');
        if (searchVal) url.searchParams.set('search', searchVal);

        fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('stat-online').innerText = data.totalOnline;
            document.getElementById('stat-dl').innerText = data.totalDownload;
            document.getElementById('stat-ul').innerText = data.totalUpload;
            document.getElementById('stat-count').innerText = data.totalOnline;
            document.getElementById('session-tbody').innerHTML = data.html;
        })
        .catch(err => console.error("Auto refresh failed", err));
    }, 1000);
</script>
@endsection
