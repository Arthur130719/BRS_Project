@extends('layouts.app')
@section('title', 'OLT & ONU Management')
@section('page-title', 'OLT & ONU')
@section('breadcrumb', 'Jaringan / OLT & ONU')

@section('content')

<style>
/* OLT & ONU specific styles */
.olt-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
    margin-bottom: 20px;
}

@media (max-width: 900px) {
    .olt-grid { grid-template-columns: 1fr; }
}

.olt-card {
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    transition: border-color var(--transition);
}

.olt-card:hover { border-color: var(--border-light); }

.olt-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 18px;
    border-bottom: 1px solid var(--border);
}

.olt-card-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.olt-icon {
    width: 42px; height: 42px;
    background: var(--indigo-dim);
    border-radius: var(--radius);
    display: flex; align-items: center; justify-content: center;
    color: #a5b4fc;
    font-size: 18px;
    flex-shrink: 0;
}

.olt-name {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-1);
}

.olt-meta {
    font-size: 11px;
    color: var(--text-3);
    margin-top: 2px;
    font-family: 'JetBrains Mono', monospace;
}

/* Port visualization grid */
.port-grid-wrap {
    padding: 14px 18px;
    border-bottom: 1px solid var(--border);
}

.port-grid-label {
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.7px;
    color: var(--text-4);
    margin-bottom: 8px;
}

.port-grid {
    display: grid;
    grid-template-columns: repeat(8, 1fr);
    gap: 4px;
}

.port-sq {
    aspect-ratio: 1;
    border-radius: 3px;
    cursor: default;
    position: relative;
    transition: transform var(--transition);
}

.port-sq:hover { transform: scale(1.15); z-index: 2; }

.port-sq.port-up     { background: var(--green); box-shadow: 0 0 6px rgba(16,185,129,0.4); }
.port-sq.port-down   { background: var(--red);   box-shadow: 0 0 4px rgba(239,68,68,0.3); }
.port-sq.port-unused { background: var(--border); }

.port-sq[title] { cursor: help; }

/* OLT port stats */
.olt-port-stats {
    display: flex;
    gap: 0;
    padding: 10px 18px;
}

.olt-stat-item {
    flex: 1;
    text-align: center;
    padding: 6px 0;
}

.olt-stat-item + .olt-stat-item {
    border-left: 1px solid var(--border);
}

.olt-stat-val {
    font-size: 18px;
    font-weight: 700;
    font-family: 'JetBrains Mono', monospace;
}

.olt-stat-lbl {
    font-size: 10px;
    color: var(--text-4);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 2px;
}

/* Signal bars */
.signal-bars {
    display: inline-flex;
    align-items: flex-end;
    gap: 2px;
    height: 14px;
}

.signal-bar {
    width: 4px;
    border-radius: 1px;
    background: var(--border);
}

.signal-bars .signal-bar:nth-child(1) { height: 4px; }
.signal-bars .signal-bar:nth-child(2) { height: 6px; }
.signal-bars .signal-bar:nth-child(3) { height: 9px; }
.signal-bars .signal-bar:nth-child(4) { height: 12px; }

.signal-bars.sig-excellent .signal-bar { background: var(--green); }
.signal-bars.sig-good      .signal-bar:nth-child(-n+3) { background: var(--green); }
.signal-bars.sig-weak      .signal-bar:nth-child(-n+2) { background: var(--amber); }
.signal-bars.sig-poor      .signal-bar:nth-child(-n+1) { background: var(--red); }

/* rx-power coloring */
.rx-good { color: var(--green); }
.rx-warn { color: var(--amber); }
.rx-bad  { color: var(--red); }
</style>

<div class="page-header">
    <div class="page-header-title">
        <h1>OLT &amp; ONU Management</h1>
        <p>Monitoring perangkat OLT dan ONU pada jaringan fiber optik</p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('olt.index') }}" class="btn btn-ghost">
            <i class="fas fa-rotate-right"></i> Refresh
        </a>
    </div>
</div>

{{-- OLT Cards Grid --}}
@if(isset($olts) && $olts->count() > 0)
<div class="olt-grid">
    @foreach($olts as $olt)
    @php
        $totalPorts   = $olt->total_port ?? 16;
        $portsOnline  = $olt->ports_online_count ?? $olt->onus_count_online ?? 0;
        $portsOffline = $olt->ports_offline_count ?? $olt->onus_count_offline ?? 0;
        $portsUnused  = max(0, $totalPorts - $portsOnline - $portsOffline);

        // Build port status array for visualization (up to 16 ports)
        $portStatuses = [];
        for ($i = 1; $i <= $totalPorts; $i++) {
            if ($i <= $portsOnline) {
                $portStatuses[] = 'up';
            } elseif ($i <= $portsOnline + $portsOffline) {
                $portStatuses[] = 'down';
            } else {
                $portStatuses[] = 'unused';
            }
        }
    @endphp
    <div class="olt-card">
        <div class="olt-card-header">
            <div class="olt-card-info">
                <div class="olt-icon"><i class="fas fa-server"></i></div>
                <div>
                    <div class="olt-name">{{ $olt->nama }}</div>
                    <div class="olt-meta">{{ $olt->ip_address }} &bull; {{ $olt->model ?? 'Unknown Model' }}</div>
                </div>
            </div>
            @if(($olt->status ?? 'online') === 'online')
                <span class="badge badge-online">Online</span>
            @else
                <span class="badge badge-offline">Offline</span>
            @endif
        </div>

        {{-- Port Visualization --}}
        <div class="port-grid-wrap">
            <div class="port-grid-label">Port Status ({{ $totalPorts }} port)</div>
            <div class="port-grid">
                @foreach($portStatuses as $idx => $status)
                <div class="port-sq port-{{ $status }}"
                     title="Port {{ $idx + 1 }}: {{ ucfirst($status) }}">
                </div>
                @endforeach
            </div>
        </div>

        {{-- Stats --}}
        <div class="olt-port-stats">
            <div class="olt-stat-item">
                <div class="olt-stat-val" style="color:var(--green);">{{ $portsOnline }}</div>
                <div class="olt-stat-lbl">Online</div>
            </div>
            <div class="olt-stat-item">
                <div class="olt-stat-val" style="color:var(--red);">{{ $portsOffline }}</div>
                <div class="olt-stat-lbl">Offline</div>
            </div>
            <div class="olt-stat-item">
                <div class="olt-stat-val" style="color:var(--text-4);">{{ $portsUnused }}</div>
                <div class="olt-stat-lbl">Unused</div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="card" style="margin-bottom:20px;">
    <div class="card-body" style="text-align:center; padding:36px;">
        <i class="fas fa-server" style="font-size:32px; color:var(--border-light); display:block; margin-bottom:12px;"></i>
        <div style="font-weight:600; color:var(--text-3); margin-bottom:4px;">Tidak Ada OLT Terdaftar</div>
        <div style="font-size:12px; color:var(--text-4);">Belum ada perangkat OLT yang dikonfigurasi.</div>
    </div>
</div>
@endif

{{-- ONU Table --}}
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Daftar ONU</div>
            <div class="card-subtitle">Semua unit ONU yang terdaftar di jaringan</div>
        </div>
        <div style="display:flex; gap:8px; align-items:center;">
            <form method="GET" action="{{ route('olt.index') }}" class="toolbar-search">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Cari SN / pelanggan..." value="{{ request('search') }}">
            </form>
        </div>
    </div>

    <div class="card-body-flush">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Serial Number</th>
                        <th>Pelanggan</th>
                        <th>OLT</th>
                        <th>Port</th>
                        <th>RX Power</th>
                        <th>TX Power</th>
                        <th>Sinyal</th>
                        <th>Status</th>
                        <th>Model</th>
                        <th>Uptime</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($onus ?? [] as $onu)
                    @php
                        $rxPower = $onu->rx_power ?? null;
                        $rxClass = '';
                        $sigClass = 'sig-excellent';

                        if ($rxPower !== null) {
                            if ($rxPower >= -20) {
                                $rxClass  = 'rx-good';
                                $sigClass = 'sig-excellent';
                            } elseif ($rxPower >= -25) {
                                $rxClass  = 'rx-good';
                                $sigClass = 'sig-good';
                            } elseif ($rxPower >= -27) {
                                $rxClass  = 'rx-warn';
                                $sigClass = 'sig-weak';
                            } else {
                                $rxClass  = 'rx-bad';
                                $sigClass = 'sig-poor';
                            }
                        }
                    @endphp
                    <tr>
                        <td><span class="mono">{{ $onu->serial_number ?? '-' }}</span></td>
                        <td>
                            @if(isset($onu->pelanggan))
                                <div style="font-weight:500; color:var(--text-1);">{{ $onu->pelanggan->nama ?? '-' }}</div>
                                <div class="mono-mute" style="font-size:10px;">{{ $onu->pelanggan->kode ?? '' }}</div>
                            @else
                                <span style="color:var(--text-4); font-size:12px;">—</span>
                            @endif
                        </td>
                        <td>
                            <span style="font-size:12px; color:var(--text-2);">{{ $onu->olt->nama ?? '-' }}</span>
                        </td>
                        <td><span class="mono">{{ $onu->port ?? '-' }}</span></td>
                        <td>
                            @if($rxPower !== null)
                                <span class="mono {{ $rxClass }}">{{ number_format($rxPower, 2) }} dBm</span>
                            @else
                                <span class="mono-mute">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="mono-mute">
                                {{ $onu->tx_power !== null ? number_format($onu->tx_power, 2) . ' dBm' : '—' }}
                            </span>
                        </td>
                        <td>
                            <div class="signal-bars {{ $sigClass }}">
                                <div class="signal-bar"></div>
                                <div class="signal-bar"></div>
                                <div class="signal-bar"></div>
                                <div class="signal-bar"></div>
                            </div>
                        </td>
                        <td>
                            @if(($onu->status ?? '') === 'online')
                                <span class="badge badge-online">Online</span>
                            @else
                                <span class="badge badge-offline">Offline</span>
                            @endif
                        </td>
                        <td>
                            <span style="font-size:12px; color:var(--text-2);">{{ $onu->model ?? '-' }}</span>
                        </td>
                        <td>
                            <span class="mono-mute">{{ $onu->uptime ?? '-' }}</span>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('olt.reboot', $onu->id) }}"
                                  onsubmit="return confirm('Reboot ONU {{ $onu->serial_number }}? Pelanggan akan terputus sementara.')">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-xs">
                                    <i class="fas fa-power-off"></i> Reboot
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11">
                            <div style="text-align:center; padding:48px 20px; color:var(--text-4);">
                                <i class="fas fa-circle-nodes" style="font-size:36px; margin-bottom:12px; display:block; color:var(--border-light);"></i>
                                <div style="font-weight:600; color:var(--text-3); margin-bottom:4px;">Tidak Ada ONU Ditemukan</div>
                                <div style="font-size:12px;">Belum ada perangkat ONU yang terdaftar pada sistem.</div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(isset($onus) && method_exists($onus, 'hasPages') && $onus->hasPages())
    <div style="padding:12px 16px; border-top:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
        <span class="mono-mute">
            Menampilkan {{ $onus->firstItem() }}–{{ $onus->lastItem() }} dari {{ $onus->total() }} ONU
        </span>
        <div style="display:flex; gap:4px;">
            @if($onus->onFirstPage())
                <span class="btn btn-ghost btn-xs" style="opacity:0.4; cursor:not-allowed;"><i class="fas fa-chevron-left"></i></span>
            @else
                <a href="{{ $onus->previousPageUrl() }}" class="btn btn-ghost btn-xs"><i class="fas fa-chevron-left"></i></a>
            @endif
            @foreach($onus->getUrlRange(max(1, $onus->currentPage()-2), min($onus->lastPage(), $onus->currentPage()+2)) as $page => $url)
                <a href="{{ $url }}" class="btn btn-xs {{ $page == $onus->currentPage() ? 'btn-primary' : 'btn-ghost' }}">{{ $page }}</a>
            @endforeach
            @if($onus->hasMorePages())
                <a href="{{ $onus->nextPageUrl() }}" class="btn btn-ghost btn-xs"><i class="fas fa-chevron-right"></i></a>
            @else
                <span class="btn btn-ghost btn-xs" style="opacity:0.4; cursor:not-allowed;"><i class="fas fa-chevron-right"></i></span>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection
