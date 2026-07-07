@extends('layouts.app')
@section('title', 'Log Isolir')
@section('page-title', 'Log Isolir')
@section('breadcrumb', 'Keuangan / Log Isolir')

@section('content')
@php
  $totalIsolir = \App\Models\IsolirLog::where('aksi','isolir')->count();
  $totalAuto   = \App\Models\IsolirLog::where('metode','auto')->count();
  $totalAktif  = \App\Models\IsolirLog::where('aksi','aktifkan')->count();
@endphp
<div class="page-header">
  <div class="page-header-title">
    <h1>Riwayat Isolir</h1>
    <p>Log semua aksi isolir dan reaktivasi pelanggan</p>
  </div>
</div>

{{-- Quick stats --}}
<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);">
  <div class="stat-card amber">
    <div class="stat-icon amber"><i class="fas fa-lock"></i></div>
    <div class="stat-value">{{ $totalIsolir }}</div>
    <div class="stat-label">Total Isolir</div>
  </div>
  <div class="stat-card indigo">
    <div class="stat-icon indigo"><i class="fas fa-robot"></i></div>
    <div class="stat-value">{{ $totalAuto }}</div>
    <div class="stat-label">Auto-Isolir</div>
  </div>
  <div class="stat-card green">
    <div class="stat-icon green"><i class="fas fa-lock-open"></i></div>
    <div class="stat-value">{{ $totalAktif }}</div>
    <div class="stat-label">Diaktifkan</div>
  </div>
</div>

<div class="card">
  <div class="card-body-flush">
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>Waktu</th>
            <th>Pelanggan</th>
            <th>Aksi</th>
            <th>Metode</th>
            <th>Invoice</th>
            <th>Oleh</th>
            <th>Alasan</th>
          </tr>
        </thead>
        <tbody>
          @forelse($logs as $log)
          <tr>
            <td class="mono-mute" style="white-space:nowrap;">{{ $log->created_at->format('d/m/Y H:i') }}</td>
            <td>
              @if($log->pelanggan)
              <a href="{{ route('pelanggan.show', $log->pelanggan_id) }}" style="color:var(--text-1); text-decoration:none; font-weight:500;">
                {{ $log->pelanggan->nama }}
              </a>
              <div class="mono-mute" style="font-size:11px;">{{ $log->pelanggan->username_pppoe }}</div>
              @else
                <span class="text-mute">—</span>
              @endif
            </td>
            <td>
              @if($log->aksi === 'isolir')
                <span class="badge badge-suspend"><i class="fas fa-lock" style="font-size:9px;"></i> ISOLIR</span>
              @else
                <span class="badge badge-active"><i class="fas fa-lock-open" style="font-size:9px;"></i> AKTIFKAN</span>
              @endif
            </td>
            <td>
              @if($log->metode === 'auto')
                <span class="badge badge-auto"><i class="fas fa-robot" style="font-size:9px;"></i> AUTO</span>
              @else
                <span class="badge badge-manual"><i class="fas fa-hand-pointer" style="font-size:9px;"></i> MANUAL</span>
              @endif
            </td>
            <td>
              @if($log->invoice)
                <span class="mono" style="font-size:11px;">{{ $log->invoice->no_invoice }}</span>
              @else
                <span class="text-mute">—</span>
              @endif
            </td>
            <td>
              @if($log->user)
                <span style="font-size:12px;">{{ $log->user->name }}</span>
              @else
                <span class="mono-mute">Sistem</span>
              @endif
            </td>
            <td style="color:var(--text-3); font-size:12px; max-width:200px;">{{ $log->alasan ?? '—' }}</td>
          </tr>
          @empty
          <tr>
            <td colspan="7">
              <div class="empty-state">
                <i class="fas fa-clock-rotate-left"></i>
                <h3>Belum ada riwayat isolir</h3>
                <p>Log akan muncul setelah ada aksi isolir atau reaktivasi</p>
              </div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    {{ $logs->links() }}
  </div>
</div>
@endsection
