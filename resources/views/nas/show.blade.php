@extends('layouts.app')
@section('title', 'Detail NAS: ' . $nas->nama)
@section('page-title', 'Detail NAS')
@section('breadcrumb', 'Jaringan / NAS Router / ' . $nas->kode)

@php
if (!function_exists('formatBytes')) {
    function formatBytes($bytes, $precision = 2) {
        $bytes = (float) $bytes;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
@endphp

@section('content')
<div class="page-header" style="margin-bottom:24px;">
    <div>
        <h1 style="font-size:24px; font-weight:700; color:var(--text-1); margin-bottom:4px;">{{ $nas->nama }} ({{ $nas->kode }})</h1>
        <p style="color:var(--text-3); font-size:13px; margin:0;">
            <i class="fas fa-network-wired" style="margin-right:4px;"></i> {{ $nas->ip_address }} 
            @if($nas->model) &bull; {{ $nas->model }} @endif
        </p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="{{ route('nas.index') }}" class="btn btn-ghost">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <a href="{{ route('nas.show', $nas->id) }}" class="btn btn-primary">
            <i class="fas fa-rotate-right"></i> Refresh Data
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Sesi PPPoE Aktif</div>
            <div class="card-subtitle">Menampilkan {{ count($activeSessions) }} pelanggan yang sedang terhubung ke router ini secara real-time.</div>
        </div>
    </div>
    
    <div class="card-body-flush table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th style="width:50px;">#</th>
                    <th>Nama / Secret</th>
                    <th>IP Address</th>
                    <th>MAC Address</th>
                    <th>Uptime</th>
                    <th>Traffic (Upload)</th>
                    <th>Traffic (Download)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activeSessions as $index => $session)
                <tr>
                    <td class="mono-mute">{{ $index + 1 }}</td>
                    <td>
                        <strong style="color:var(--text-1);">{{ $session['name'] ?? '-' }}</strong>
                    </td>
                    <td class="mono">{{ $session['address'] ?? '-' }}</td>
                    <td class="mono-mute">{{ $session['caller-id'] ?? '-' }}</td>
                    <td class="mono" style="color:var(--text-2);">{{ $session['uptime'] ?? '-' }}</td>
                    <td>
                        @if(isset($session['rx-rate']))
                            <div style="color:var(--sky); font-weight:600; font-family:'JetBrains Mono', monospace; font-size:12px;">
                                <i class="fas fa-arrow-up" style="font-size:10px; margin-right:4px;"></i> 
                                {{ formatBytes($session['rx-rate'] ?? 0) }}/s
                            </div>
                            <div class="mono-mute" style="font-size:10px; margin-top:2px;">
                                Total: {{ formatBytes($session['rx-byte'] ?? 0) }}
                            </div>
                        @else
                            <span class="text-mute">-</span>
                        @endif
                    </td>
                    <td>
                        @if(isset($session['tx-rate']))
                            <div style="color:var(--green); font-weight:600; font-family:'JetBrains Mono', monospace; font-size:12px;">
                                <i class="fas fa-arrow-down" style="font-size:10px; margin-right:4px;"></i> 
                                {{ formatBytes($session['tx-rate'] ?? 0) }}/s
                            </div>
                            <div class="mono-mute" style="font-size:10px; margin-top:2px;">
                                Total: {{ formatBytes($session['tx-byte'] ?? 0) }}
                            </div>
                        @else
                            <span class="text-mute">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state" style="padding:40px; text-align:center;">
                            <i class="fas fa-users-slash" style="font-size:36px; color:var(--border-light); margin-bottom:12px;"></i>
                            <h3 style="font-size:16px; font-weight:600; color:var(--text-3); margin-bottom:4px;">Tidak Ada Sesi Aktif</h3>
                            <p style="font-size:13px; color:var(--text-4);">Belum ada pelanggan PPPoE yang terkoneksi ke router ini.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
