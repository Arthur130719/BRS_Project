@extends('layouts.app')

@section('title', 'Info Database')
@section('page-title', 'Info Database')
@section('breadcrumb', 'Sistem / Info Database')

@section('content')

<style>
/* ── Arch diagram ── */
.arch-box {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    min-width: 110px;
}
.arch-node {
    padding: 10px 16px;
    border-radius: var(--radius);
    border: 1px solid;
    font-size: 12px;
    font-weight: 600;
    font-family: 'JetBrains Mono', monospace;
    text-align: center;
    white-space: nowrap;
}
.arch-node.sky    { background: rgba(14,165,233,0.12);  border-color: rgba(14,165,233,0.35); color: #7dd3fc; }
.arch-node.indigo { background: rgba(99,102,241,0.12);  border-color: rgba(99,102,241,0.35); color: #a5b4fc; }
.arch-node.amber  { background: rgba(245,158,11,0.12);  border-color: rgba(245,158,11,0.35); color: #fcd34d; }
.arch-node.red    { background: rgba(239,68,68,0.12);   border-color: rgba(239,68,68,0.35);  color: #fca5a5; }
.arch-node.green  { background: rgba(16,185,129,0.12);  border-color: rgba(16,185,129,0.35); color: #6ee7b7; }
.arch-label {
    font-size: 10px;
    color: var(--text-4);
    text-align: center;
    font-family: 'JetBrains Mono', monospace;
}
.arch-arrow {
    color: var(--text-4);
    font-size: 18px;
    display: flex;
    align-items: center;
    padding: 0 4px;
    margin-top: -6px;
}
.arch-arrow-down {
    color: var(--text-4);
    font-size: 18px;
    display: flex;
    justify-content: center;
    margin: 2px 0;
}
/* ── Progress bar in table ── */
.db-progress {
    height: 6px;
    background: var(--bg-elevated);
    border-radius: 3px;
    overflow: hidden;
    margin-top: 4px;
    min-width: 80px;
}
.db-progress-fill {
    height: 100%;
    border-radius: 3px;
    background: linear-gradient(90deg, var(--indigo-dark), var(--indigo));
    transition: width 0.5s ease;
}
/* ── Connection status card ── */
.conn-card {
    background: var(--bg-elevated);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 16px;
    flex: 1;
}
.conn-card-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-1);
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
}
.online-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: var(--green);
    box-shadow: 0 0 6px var(--green);
    animation: blink 2s infinite;
    flex-shrink: 0;
}
.offline-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: var(--red);
    flex-shrink: 0;
}
/* ── Table stats grid ── */
.tbl-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
}
.tbl-stat-item {
    background: var(--bg-elevated);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 14px;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
}
.tbl-stat-icon {
    width: 32px; height: 32px;
    border-radius: var(--radius-sm);
    display: flex; align-items: center; justify-content: center;
    font-size: 14px;
    margin-bottom: 4px;
}
.tbl-stat-count {
    font-family: 'JetBrains Mono', monospace;
    font-size: 22px;
    font-weight: 700;
    color: var(--text-1);
    line-height: 1;
}
.tbl-stat-label {
    font-size: 12px;
    color: var(--text-3);
}
.tbl-stat-table {
    font-family: 'JetBrains Mono', monospace;
    font-size: 10px;
    color: var(--text-4);
}
/* Index type badge */
.idx-badge {
    display: inline-flex;
    padding: 2px 7px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 700;
    font-family: 'JetBrains Mono', monospace;
    letter-spacing: 0.3px;
}
.idx-primary { background: rgba(99,102,241,0.15); color: #a5b4fc; border: 1px solid rgba(99,102,241,0.3); }
.idx-unique  { background: rgba(16,185,129,0.12); color: #6ee7b7; border: 1px solid rgba(16,185,129,0.25); }
.idx-index   { background: rgba(14,165,233,0.12); color: #7dd3fc; border: 1px solid rgba(14,165,233,0.25); }
</style>

{{-- ══════════════════════════════════════════════
     PAGE HEADER
══════════════════════════════════════════════ --}}
<div class="page-header">
    <div class="page-header-title">
        <h1><i class="fas fa-database" style="color:var(--amber);margin-right:8px;"></i>Informasi Basis Data</h1>
        <p>Arsitektur sistem basis data terdistribusi BRS</p>
    </div>
    <div class="page-header-actions">
        <span class="badge badge-auto" style="padding:5px 12px;font-size:12px;">
            <i class="fas fa-leaf"></i> MySQL {{ $mysqlVersion ?? '8.0' }}
        </span>
        @if(($redisStatus ?? 'offline') === 'online')
            <span class="badge badge-active" style="padding:5px 12px;font-size:12px;">
                <i class="fas fa-memory"></i> Redis Online
            </span>
        @else
            <span class="badge badge-inactive" style="padding:5px 12px;font-size:12px;">
                <i class="fas fa-memory"></i> Redis Offline
            </span>
        @endif
    </div>
</div>

{{-- ══════════════════════════════════════════════
     ARSITEKTUR DIAGRAM
══════════════════════════════════════════════ --}}
<div class="card" style="margin-bottom:20px;">
    <div class="card-header">
        <div>
            <div class="card-title"><i class="fas fa-sitemap" style="color:var(--indigo);margin-right:6px;"></i>Arsitektur Sistem Terdistribusi</div>
            <div class="card-subtitle">Topologi container BRS ISP Management</div>
        </div>
    </div>
    <div class="card-body">
        {{-- Row 1: Client → Nginx → PHP-FPM --}}
        <div style="display:flex;align-items:center;justify-content:center;gap:8px;flex-wrap:wrap;margin-bottom:0;">

            {{-- Browser --}}
            <div class="arch-box">
                <div class="arch-node sky">
                    <i class="fas fa-globe" style="margin-right:5px;"></i>Browser
                </div>
                <div class="arch-label">Client / User</div>
            </div>

            <div class="arch-arrow"><i class="fas fa-arrow-right"></i></div>

            {{-- Nginx --}}
            <div class="arch-box">
                <div class="arch-node sky">
                    <i class="fas fa-server" style="margin-right:5px;"></i>Nginx
                    <span style="font-size:10px;opacity:0.7;"> :80/:443</span>
                </div>
                <div class="arch-label">Nginx Container</div>
            </div>

            <div class="arch-arrow"><i class="fas fa-arrow-right"></i></div>

            {{-- PHP-FPM --}}
            <div class="arch-box">
                <div class="arch-node indigo">
                    <i class="fas fa-code" style="margin-right:5px;"></i>PHP-FPM
                    <span style="font-size:10px;opacity:0.7;"> :9000</span>
                </div>
                <div class="arch-label">Laravel App Container</div>
            </div>
        </div>

        {{-- Arrows down from PHP-FPM --}}
        <div style="display:flex;align-items:flex-start;justify-content:center;gap:8px;margin-top:4px;">
            {{-- Spacer --}}
            <div style="flex:1;max-width:320px;"></div>

            {{-- Down arrows to MySQL and Redis --}}
            <div style="display:flex;gap:60px;margin-left:8px;">
                <div style="display:flex;flex-direction:column;align-items:center;">
                    <div class="arch-arrow-down"><i class="fas fa-arrow-down"></i></div>
                    <div class="arch-box">
                        <div class="arch-node amber">
                            <i class="fas fa-database" style="margin-right:5px;"></i>MySQL
                            <span style="font-size:10px;opacity:0.7;"> :3306</span>
                        </div>
                        <div class="arch-label">Database Container</div>
                    </div>
                    <div class="arch-arrow-down"><i class="fas fa-arrow-down"></i></div>
                    <div class="arch-box">
                        <div class="arch-node green">
                            <i class="fas fa-hdd" style="margin-right:5px;"></i>Volume
                        </div>
                        <div class="arch-label">Persistent Storage</div>
                    </div>
                </div>

                <div style="display:flex;flex-direction:column;align-items:center;">
                    <div class="arch-arrow-down"><i class="fas fa-arrow-down"></i></div>
                    <div class="arch-box">
                        <div class="arch-node red">
                            <i class="fas fa-memory" style="margin-right:5px;"></i>Redis
                            <span style="font-size:10px;opacity:0.7;"> :6379</span>
                        </div>
                        <div class="arch-label">Cache / Session</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Legend --}}
        <div style="display:flex;gap:16px;justify-content:center;margin-top:18px;flex-wrap:wrap;">
            <span style="font-size:11px;color:var(--text-4);display:flex;align-items:center;gap:5px;">
                <span style="width:10px;height:10px;background:var(--sky);border-radius:2px;display:inline-block;"></span> Network Layer
            </span>
            <span style="font-size:11px;color:var(--text-4);display:flex;align-items:center;gap:5px;">
                <span style="width:10px;height:10px;background:var(--indigo);border-radius:2px;display:inline-block;"></span> Application Layer
            </span>
            <span style="font-size:11px;color:var(--text-4);display:flex;align-items:center;gap:5px;">
                <span style="width:10px;height:10px;background:var(--amber);border-radius:2px;display:inline-block;"></span> Persistence Layer
            </span>
            <span style="font-size:11px;color:var(--text-4);display:flex;align-items:center;gap:5px;">
                <span style="width:10px;height:10px;background:var(--red);border-radius:2px;display:inline-block;"></span> Cache Layer
            </span>
            <span style="font-size:11px;color:var(--text-4);display:flex;align-items:center;gap:5px;">
                <span style="width:10px;height:10px;background:var(--green);border-radius:2px;display:inline-block;"></span> Storage Layer
            </span>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════
     TABLE STATS GRID
══════════════════════════════════════════════ --}}
<div class="card" style="margin-bottom:20px;">
    <div class="card-header">
        <div>
            <div class="card-title"><i class="fas fa-table" style="color:var(--sky);margin-right:6px;"></i>Jumlah Record per Tabel</div>
            <div class="card-subtitle">Statistik data aktif dalam database</div>
        </div>
    </div>
    <div class="card-body">
        <div class="tbl-stats-grid">
            @php
                $statIcons = [
                    'users'        => ['fas fa-users',         'indigo', 'Pengguna'],
                    'pelanggan'    => ['fas fa-id-card',       'green',  'Pelanggan'],
                    'invoice'      => ['fas fa-file-invoice',  'amber',  'Invoice'],
                    'pembayaran'   => ['fas fa-money-bill',    'green',  'Pembayaran'],
                    'paket'        => ['fas fa-box',           'sky',    'Paket'],
                    'nas'          => ['fas fa-network-wired', 'indigo', 'NAS'],
                    'olt'          => ['fas fa-broadcast-tower','amber', 'OLT'],
                    'activity_log' => ['fas fa-scroll',        'red',    'Activity Log'],
                ];
                $colorStyleMap = [
                    'indigo' => 'background:rgba(99,102,241,0.12);color:#a5b4fc;',
                    'green'  => 'background:rgba(16,185,129,0.12);color:#6ee7b7;',
                    'amber'  => 'background:rgba(245,158,11,0.12);color:#fcd34d;',
                    'sky'    => 'background:rgba(14,165,233,0.12);color:#7dd3fc;',
                    'red'    => 'background:rgba(239,68,68,0.12);color:#fca5a5;',
                    'purple' => 'background:rgba(168,85,247,0.12);color:#d8b4fe;',
                ];
            @endphp
            @foreach($tableStats as $stat)
                @php
                    $iconClass = 'fas ' . $stat['icon'];
                    $style     = $colorStyleMap[$stat['color']] ?? $colorStyleMap['indigo'];
                @endphp
                <div class="tbl-stat-item">
                    <div class="tbl-stat-icon" style="{{ $style }}">
                        <i class="{{ $iconClass }}"></i>
                    </div>
                    <div class="tbl-stat-count">{{ number_format($stat['count']) }}</div>
                    <div class="tbl-stat-label">{{ $stat['label'] }}</div>
                    <div class="tbl-stat-table">{{ $stat['table'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════
     UKURAN TABEL + INDEX (2 kolom)
══════════════════════════════════════════════ --}}
<div class="grid-2" style="gap:20px;margin-bottom:20px;" x-data="{ indexFilter: '' }">

    {{-- Ukuran Database --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title"><i class="fas fa-weight-hanging" style="color:var(--amber);margin-right:6px;"></i>Ukuran Database</div>
                <div class="card-subtitle">
                    Total:
                    <span class="mono" style="color:var(--amber);">
                        {{ $dbTotalSize ?? '—' }}
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body-flush">
            @php
                $maxSize = collect($tableSizes ?? [])->max('size_kb') ?: 1;
            @endphp
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama Tabel</th>
                            <th class="text-right">Ukuran</th>
                            <th class="text-right">Est. Rows</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tableSizes ?? [] as $tbl)
                        <tr>
                            <td>
                                <span class="mono-mute">{{ $tbl['table'] }}</span>
                                <div class="db-progress">
                                    <div class="db-progress-fill"
                                         style="width:{{ min(100, round(($tbl['size_kb'] / $maxSize) * 100)) }}%"></div>
                                </div>
                            </td>
                            <td class="text-right">
                                <span class="mono" style="color:var(--amber);">
                                    {{ number_format($tbl['size_kb'], 1) }} KB
                                </span>
                            </td>
                            <td class="text-right">
                                <span class="mono-mute">{{ number_format($tbl['rows'] ?? 0) }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3">
                                <div class="empty-state" style="padding:24px;">
                                    <i class="fas fa-database"></i>
                                    <h3>Tidak ada data ukuran</h3>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Daftar Index --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title"><i class="fas fa-key" style="color:var(--indigo);margin-right:6px;"></i>Daftar Index</div>
                <div class="card-subtitle">Index terdaftar dalam database</div>
            </div>
        </div>
        <div style="padding:10px 16px;border-bottom:1px solid var(--border);">
            <div class="toolbar-search" style="width:100%;">
                <i class="fas fa-search"></i>
                <input type="text" x-model="indexFilter" placeholder="Filter nama tabel..." style="width:100%;">
            </div>
        </div>
        <div class="card-body-flush">
            <div class="table-wrap" style="max-height:320px;overflow-y:auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tabel</th>
                            <th>Nama Index</th>
                            <th>Kolom</th>
                            <th>Tipe</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dbIndexes ?? [] as $idx)
                        <tr x-show="indexFilter === '' || '{{ strtolower($idx['table']) }}'.includes(indexFilter.toLowerCase())">
                            <td><span class="mono-mute">{{ $idx['table'] }}</span></td>
                            <td><span class="mono" style="font-size:11px;">{{ $idx['key_name'] }}</span></td>
                            <td><span class="mono-mute">{{ $idx['column'] }}</span></td>
                            <td>
                                @if($idx['key_name'] === 'PRIMARY')
                                    <span class="idx-badge idx-primary">PRIMARY</span>
                                @elseif(!($idx['non_unique'] ?? true))
                                    <span class="idx-badge idx-unique">UNIQUE</span>
                                @else
                                    <span class="idx-badge idx-index">INDEX</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state" style="padding:24px;">
                                    <i class="fas fa-key"></i>
                                    <h3>Tidak ada data index</h3>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════
     STATUS KONEKSI (2 cards side-by-side)
══════════════════════════════════════════════ --}}
<div style="display:flex;gap:20px;margin-bottom:20px;flex-wrap:wrap;">

    {{-- MySQL Status --}}
    <div class="conn-card">
        <div class="conn-card-title">
            <div class="online-dot"></div>
            <i class="fas fa-database" style="color:var(--amber);"></i>
            MySQL — Status Koneksi
        </div>
        <div>
            <div class="info-row">
                <span class="key">Versi</span>
                <span class="val">{{ $mysqlVersion ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="key">Database</span>
                <span class="val">{{ config('database.connections.mysql.database') }}</span>
            </div>
            <div class="info-row">
                <span class="key">Host</span>
                <span class="val">{{ config('database.connections.mysql.host') }}:{{ config('database.connections.mysql.port') }}</span>
            </div>
            <div class="info-row">
                <span class="key">Charset</span>
                <span class="val">{{ config('database.connections.mysql.charset') }}</span>
            </div>
            <div class="info-row" style="border-bottom:none;">
                <span class="key">Status</span>
                <span style="display:flex;align-items:center;gap:6px;">
                    <span class="online-dot"></span>
                    <span style="color:var(--green);font-size:12px;font-weight:600;">Online</span>
                </span>
            </div>
        </div>
    </div>

    {{-- Redis Status --}}
    <div class="conn-card">
        @if(($redisStatus ?? 'offline') === 'online')
            <div class="conn-card-title">
                <div class="online-dot"></div>
                <i class="fas fa-memory" style="color:var(--red);"></i>
                Redis — Status Koneksi
            </div>
            <div>
                <div class="info-row">
                    <span class="key">Versi</span>
                    <span class="val">{{ $redisInfo['version'] ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="key">Memory Used</span>
                    <span class="val">{{ $redisInfo['used_memory_human'] ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="key">Connected Clients</span>
                    <span class="val">{{ $redisInfo['connected_clients'] ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="key">Uptime</span>
                    <span class="val">{{ $redisInfo['uptime_in_days'] ?? '—' }} hari</span>
                </div>
                <div class="info-row" style="border-bottom:none;">
                    <span class="key">Status</span>
                    <span style="display:flex;align-items:center;gap:6px;">
                        <span class="online-dot"></span>
                        <span style="color:var(--green);font-size:12px;font-weight:600;">Online</span>
                    </span>
                </div>
            </div>
        @else
            <div class="conn-card-title">
                <div class="offline-dot"></div>
                <i class="fas fa-memory" style="color:var(--red);"></i>
                Redis — Status Koneksi
            </div>
            <div style="padding:20px 0;text-align:center;">
                <i class="fas fa-times-circle" style="font-size:32px;color:var(--red);opacity:0.5;margin-bottom:10px;display:block;"></i>
                <div style="font-size:14px;font-weight:600;color:#fca5a5;">Redis Offline</div>
                <div style="font-size:12px;color:var(--text-4);margin-top:4px;">Tidak dapat terhubung ke Redis server</div>
            </div>
        @endif
    </div>

</div>

{{-- ══════════════════════════════════════════════
     TOMBOL BACKUP
══════════════════════════════════════════════ --}}
<div class="card">
    <div class="card-body" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div>
            <div style="font-size:14px;font-weight:600;color:var(--text-1);margin-bottom:2px;">
                <i class="fas fa-shield-alt" style="color:var(--green);margin-right:6px;"></i>
                Backup Database
            </div>
            <div style="font-size:12px;color:var(--text-3);">
                Download backup lengkap database MySQL sebagai file SQL terkompresi.
            </div>
        </div>
        <form method="POST"
              action="{{ route('pengaturan.backup') }}"
              onsubmit="return confirm('Download backup database sekarang? Proses ini mungkin membutuhkan beberapa saat.')">
            @csrf
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-database"></i>
                Backup Database
            </button>
        </form>
    </div>
</div>

@endsection
