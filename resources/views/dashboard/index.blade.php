@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('breadcrumb', 'Home / Dashboard')

@section('content')

{{-- ── Page Header ── --}}
<div class="page-header">
  <div class="page-header-title">
    <h1>Dashboard</h1>
    <p>Selamat datang kembali, {{ auth()->user()->name ?? 'Administrator' }} — Ringkasan sistem hari ini.</p>
  </div>
  <div class="page-header-actions">
    <a href="{{ route('invoice.create') }}" class="btn btn-ghost btn-sm">
      <i class="fas fa-file-invoice"></i> Buat Invoice
    </a>
    <a href="{{ route('pelanggan.create') }}" class="btn btn-primary btn-sm">
      <i class="fas fa-user-plus"></i> Tambah Pelanggan
    </a>
  </div>
</div>

{{-- ── Stats Grid ── --}}
<div class="stats-grid">

  {{-- Total Pelanggan --}}
  <div class="stat-card indigo">
    <div class="stat-icon indigo">
      <i class="fas fa-users"></i>
    </div>
    <div class="stat-value">{{ number_format($stats['total_pelanggan']) }}</div>
    <div class="stat-label">Total Pelanggan</div>
    <div class="stat-sub mute">
      <i class="fas fa-database" style="font-size:9px;"></i>
      Seluruh database
    </div>
  </div>

  {{-- Aktif --}}
  <div class="stat-card green">
    <div class="stat-icon green">
      <i class="fas fa-circle-check"></i>
    </div>
    <div class="stat-value">{{ number_format($stats['aktif']) }}</div>
    <div class="stat-label">Pelanggan Aktif</div>
    <div class="stat-sub up">
      <i class="fas fa-arrow-up" style="font-size:9px;"></i>
      @php
        $pct = $stats['total_pelanggan'] > 0
          ? round(($stats['aktif'] / $stats['total_pelanggan']) * 100, 1)
          : 0;
      @endphp
      {{ $pct }}% dari total
    </div>
  </div>

  {{-- Isolir / Suspend --}}
  <div class="stat-card amber">
    <div class="stat-icon amber">
      <i class="fas fa-ban"></i>
    </div>
    <div class="stat-value">{{ number_format($stats['suspend']) }}</div>
    <div class="stat-label">Pelanggan Isolir</div>
    <div class="stat-sub down">
      <i class="fas fa-triangle-exclamation" style="font-size:9px;"></i>
      Perlu perhatian
    </div>
  </div>

  {{-- Tagihan Belum Bayar --}}
  <div class="stat-card red">
    <div class="stat-icon red">
      <i class="fas fa-file-invoice-dollar"></i>
    </div>
    <div class="stat-value">{{ number_format($stats['invoice_unpaid']) }}</div>
    <div class="stat-label">Tagihan Belum Bayar</div>
    <div class="stat-sub down">
      <i class="fas fa-clock" style="font-size:9px;"></i>
      Menunggu pembayaran
    </div>
  </div>

</div><!-- /.stats-grid -->

{{-- ── Row 2: Charts ── --}}
<div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:16px;">

  {{-- Revenue Bar Chart --}}
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">
          <i class="fas fa-chart-bar" style="color:var(--indigo); margin-right:6px;"></i>
          Revenue Bulanan
        </div>
        <div class="card-subtitle">Pendapatan 6 bulan terakhir</div>
      </div>
      <span class="badge badge-active" style="font-size:10px;">Live</span>
    </div>
    <div class="card-body">
      <canvas id="revenueChart" height="220" style="max-height:220px;"></canvas>
    </div>
  </div>

  {{-- Paket Distribusi Doughnut --}}
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">
          <i class="fas fa-chart-pie" style="color:#a855f7; margin-right:6px;"></i>
          Distribusi Paket
        </div>
        <div class="card-subtitle">Pelanggan per paket aktif</div>
      </div>
    </div>
    <div class="card-body" style="display:flex; align-items:center; justify-content:center; gap:24px; flex-wrap:wrap;">
      <canvas id="paketChart" width="180" height="180" style="max-width:180px; max-height:180px; flex-shrink:0;"></canvas>
      <div id="paketLegend" style="display:flex; flex-direction:column; gap:6px; min-width:130px;"></div>
    </div>
  </div>

</div><!-- /.grid charts -->

{{-- ── Row 3: Recent Invoices + Notifikasi ── --}}
<div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:16px;">

  {{-- Recent Invoices --}}
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">
          <i class="fas fa-receipt" style="color:var(--sky); margin-right:6px;"></i>
          Invoice Terbaru
        </div>
        <div class="card-subtitle">5 tagihan terakhir</div>
      </div>
      <a href="{{ route('invoice.index') }}" class="btn btn-ghost btn-xs">
        Lihat Semua <i class="fas fa-arrow-right" style="font-size:9px;"></i>
      </a>
    </div>
    <div class="card-body-flush">
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>No Invoice</th>
              <th>Pelanggan</th>
              <th>Periode</th>
              <th>Nominal</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @forelse($recentInvoices ?? [] as $inv)
              <tr>
                <td>
                    {{ $inv->pelanggan->nama ?? '-' }}
                </td>
                <td>
                  <div style="font-size:13px; color:var(--text-1); font-weight:500;">
                    {{ $inv->pelanggan->nama ?? '-' }}
                  </div>
                  <div style="font-size:11px; color:var(--text-3);">
                    {{ $inv->pelanggan->username_pppoe ?? '' }}
                  </div>
                </td>
                <td>
                  <span class="mono-mute">{{ $inv->periode ?? '-' }}</span>
                </td>
                <td>
                  <span class="mono" style="color:var(--text-1);">
                    Rp {{ number_format($inv->nominal ?? 0, 0, ',', '.') }}
                  </span>
                </td>
                <td>
                  @if(($inv->status ?? '') === 'paid')
                    <span class="badge badge-paid">Lunas</span>
                  @elseif(($inv->status ?? '') === 'partial')
                    <span class="badge badge-partial">Sebagian</span>
                  @else
                    <span class="badge badge-unpaid">Belum Bayar</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" style="text-align:center; padding:32px 14px;">
                  <div style="color:var(--text-4);">
                    <i class="fas fa-file-invoice" style="font-size:28px; display:block; margin-bottom:8px; opacity:0.4;"></i>
                    <div style="font-size:13px;">Belum ada invoice</div>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- Notifikasi Terbaru --}}
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">
          <i class="fas fa-bell" style="color:var(--amber); margin-right:6px;"></i>
          Notifikasi Terbaru
        </div>
        <div class="card-subtitle">5 aktivitas sistem terakhir</div>
      </div>
      <a href="{{ route('notifikasi.index') }}" class="btn btn-ghost btn-xs">
        Lihat Semua <i class="fas fa-arrow-right" style="font-size:9px;"></i>
      </a>
    </div>
    <div class="card-body-flush">
      @forelse($notifikasi ?? [] as $notif)
        <div style="
          display:flex; align-items:flex-start; gap:12px;
          padding:12px 16px;
          border-bottom:1px solid rgba(51,65,85,0.4);
          transition:background var(--transition);
        " onmouseenter="this.style.background='rgba(255,255,255,0.02)'" onmouseleave="this.style.background='transparent'">

          {{-- Icon --}}
          <div style="
            width:34px; height:34px; border-radius:8px; flex-shrink:0;
            display:flex; align-items:center; justify-content:center; font-size:13px;
            {{ match($notif->type ?? 'info') {
              'success' => 'background:rgba(16,185,129,0.12); color:#6ee7b7;',
              'warning' => 'background:rgba(245,158,11,0.12); color:#fcd34d;',
              'danger'  => 'background:rgba(239,68,68,0.12); color:#fca5a5;',
              default   => 'background:rgba(99,102,241,0.12); color:#a5b4fc;',
            } }}
          ">
            @switch($notif->type ?? 'info')
              @case('success') <i class="fas fa-check-circle"></i> @break
              @case('warning') <i class="fas fa-triangle-exclamation"></i> @break
              @case('danger')  <i class="fas fa-times-circle"></i> @break
              @default         <i class="fas fa-info-circle"></i>
            @endswitch
          </div>

          {{-- Content --}}
          <div style="flex:1; min-width:0;">
            <div style="font-size:13px; font-weight:500; color:var(--text-1); margin-bottom:2px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
              {{ $notif->title ?? 'Notifikasi Sistem' }}
            </div>
            <div style="font-size:12px; color:var(--text-3); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
              {{ $notif->deskripsi ?? '-' }}
            </div>
          </div>

          {{-- Time --}}
          <div style="font-size:10px; color:var(--text-4); font-family:'JetBrains Mono',monospace; flex-shrink:0; padding-top:2px;">
            {{ isset($notif->created_at) ? $notif->created_at->diffForHumans() : '-' }}
          </div>
        </div>
      @empty
        <div style="padding:40px 16px; text-align:center; color:var(--text-4);">
          <i class="fas fa-bell-slash" style="font-size:28px; display:block; margin-bottom:8px; opacity:0.4;"></i>
          <div style="font-size:13px;">Tidak ada notifikasi</div>
        </div>
      @endforelse
    </div>
  </div>

</div><!-- /.grid invoices+notif -->

{{-- ── Auto-Isolir Info Bar ── --}}
<div style="
  display:flex; align-items:center; gap:14px; flex-wrap:wrap;
  padding:12px 16px;
  background:var(--bg-surface);
  border:1px solid var(--border);
  border-radius:var(--radius-lg);
  border-left:3px solid var(--indigo);
">
  <div style="display:flex; align-items:center; gap:8px; color:#a5b4fc; font-size:13px; font-weight:600;">
    <i class="fas fa-robot"></i>
    Auto-Isolir Engine
  </div>
  <div style="width:1px; height:16px; background:var(--border);"></div>
  <div style="font-size:12px; color:var(--text-3);">
    Terakhir dijalankan:
    <span class="mono" style="color:var(--text-2); margin-left:4px;">
      {{ $autoIsolirLastRun ?? 'Belum pernah dijalankan' }}
    </span>
  </div>
  <div style="margin-left:auto; display:flex; gap:8px;">
    <div style="display:flex; align-items:center; gap:5px; font-size:11px; color:var(--text-3);">
      <div style="width:6px; height:6px; border-radius:50%; background:var(--green); box-shadow:0 0 5px var(--green); animation:blink 2s infinite;"></div>
      Scheduler Aktif
    </div>
    <a href="{{ route('pengaturan.index') }}" class="btn btn-ghost btn-xs">
      <i class="fas fa-gear"></i> Konfigurasi
    </a>
  </div>
</div><!-- /.auto-isolir bar -->

@endsection

@push('scripts')
<script>
(function() {
  // ── Data from server ──
  const revenueData      = @json($revenue ?? []);
  const paketData        = @json($paketDistribusi ?? []);
  const DOUGHNUT_COLORS  = ['#6366f1','#10b981','#f59e0b','#ef4444','#0ea5e9','#a855f7','#ec4899'];

  // ── Chart defaults ──
  Chart.defaults.color              = '#94a3b8';
  Chart.defaults.borderColor        = 'rgba(51,65,85,0.4)';
  Chart.defaults.font.family        = "'Inter', sans-serif";
  Chart.defaults.font.size          = 11;

  // ══════════════════════════════════
  // Revenue Bar Chart
  // ══════════════════════════════════
  const revCtx = document.getElementById('revenueChart');
  if (revCtx) {
    const labels  = revenueData.map(d => d.label ?? d.bulan ?? d.month ?? '');
    const amounts = revenueData.map(d => d.nominal ?? d.total ?? d.amount ?? 0);

    new Chart(revCtx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: 'Revenue',
          data: amounts,
          backgroundColor: 'rgba(99,102,241,0.6)',
          borderColor: '#6366f1',
          borderWidth: 1.5,
          borderRadius: 4,
          borderSkipped: false,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#1e293b',
            borderColor: '#334155',
            borderWidth: 1,
            titleColor: '#f1f5f9',
            bodyColor: '#94a3b8',
            callbacks: {
              label: function(ctx) {
                const val = ctx.parsed.y;
                return ' Rp ' + new Intl.NumberFormat('id-ID').format(val);
              }
            }
          }
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: { color: '#94a3b8' },
            border: { color: 'rgba(51,65,85,0.4)' }
          },
          y: {
            grid: { color: 'rgba(51,65,85,0.35)', drawBorder: false },
            ticks: {
              color: '#94a3b8',
              callback: function(val) {
                if (val >= 1000000) return 'Rp ' + (val/1000000).toFixed(1) + 'Jt';
                if (val >= 1000)    return 'Rp ' + (val/1000).toFixed(0) + 'Rb';
                return 'Rp ' + val;
              }
            },
            border: { dash: [4, 4], color: 'transparent' }
          }
        }
      }
    });
  }

  // ══════════════════════════════════
  // Paket Distribusi Doughnut
  // ══════════════════════════════════
  const paketCtx = document.getElementById('paketChart');
  if (paketCtx) {
    const paketLabels = paketData.map(d => d.nama_paket ?? d.paket ?? d.label ?? d.name ?? 'Paket');
    const paketValues = paketData.map(d => d.jumlah ?? d.total ?? d.value ?? d.count ?? 0);
    const colors      = DOUGHNUT_COLORS.slice(0, paketLabels.length);

    const doughnut = new Chart(paketCtx, {
      type: 'doughnut',
      data: {
        labels: paketLabels,
        datasets: [{
          data: paketValues,
          backgroundColor: colors,
          borderColor: '#1e293b',
          borderWidth: 2,
          hoverOffset: 6,
        }]
      },
      options: {
        responsive: false,
        cutout: '68%',
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#1e293b',
            borderColor: '#334155',
            borderWidth: 1,
            titleColor: '#f1f5f9',
            bodyColor: '#94a3b8',
            callbacks: {
              label: function(ctx) {
                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                const pct   = total > 0 ? Math.round((ctx.parsed / total) * 100) : 0;
                return ' ' + ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
              }
            }
          }
        }
      }
    });

    // Build custom legend
    const legendEl = document.getElementById('paketLegend');
    if (legendEl) {
      const total = paketValues.reduce((a, b) => a + b, 0);
      paketLabels.forEach((label, i) => {
        const pct  = total > 0 ? Math.round((paketValues[i] / total) * 100) : 0;
        const item = document.createElement('div');
        item.style.cssText = 'display:flex; align-items:center; gap:8px; cursor:pointer;';
        item.innerHTML = `
          <div style="width:10px; height:10px; border-radius:2px; background:${colors[i]}; flex-shrink:0;"></div>
          <div>
            <div style="font-size:12px; color:#f1f5f9; font-weight:500; line-height:1.2;">${label}</div>
            <div style="font-size:10px; color:#64748b; font-family:'JetBrains Mono',monospace;">${paketValues[i]} pelanggan · ${pct}%</div>
          </div>
        `;
        item.addEventListener('mouseenter', () => {
          doughnut.setDatasetVisibility(0, true);
        });
        legendEl.appendChild(item);
      });

      if (paketLabels.length === 0) {
        legendEl.innerHTML = '<div style="font-size:12px; color:#64748b;">Belum ada data paket</div>';
      }
    }
  }

})();
</script>
@endpush
