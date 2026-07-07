@extends('layouts.app')
@section('title', 'Detail Invoice')
@section('page-title', 'Detail Invoice')
@section('breadcrumb', 'Invoice / Detail')

@section('content')
<div class="page-header">
  <div class="page-header-title">
    <h1>Invoice <span class="mono" style="font-size:18px;">{{ $invoice->no_invoice }}</span></h1>
    <p>Periode {{ $invoice->periode }} — {{ $invoice->pelanggan->nama }}</p>
  </div>
  <div class="page-header-actions">
    <a href="{{ route('invoice.pdf', $invoice->id) }}" class="btn btn-ghost" target="_blank">
      <i class="fas fa-file-pdf"></i> Export PDF
    </a>
    @if($invoice->status !== 'paid')
      <form method="POST" action="{{ route('invoice.lunas', $invoice->id) }}" style="display:inline">
        @csrf
        <button type="submit" class="btn btn-success" onclick="return confirm('Tandai invoice ini sebagai lunas?')">
          <i class="fas fa-check-double"></i> Tandai Lunas
        </button>
      </form>
    @endif
    <a href="{{ route('invoice.index') }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Kembali</a>
  </div>
</div>

<div class="grid-2 mb-4">
  <!-- Invoice Info -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">Informasi Invoice</div>
      @if($invoice->status === 'paid')
        <span class="badge badge-paid">LUNAS</span>
      @elseif($invoice->status === 'unpaid')
        <span class="badge badge-unpaid">BELUM BAYAR</span>
      @else
        <span class="badge badge-partial">PARSIAL</span>
      @endif
    </div>
    <div class="card-body">
      <div class="info-row"><span class="key">No. Invoice</span><span class="val">{{ $invoice->no_invoice }}</span></div>
      <div class="info-row"><span class="key">Periode</span><span class="val">{{ $invoice->periode }}</span></div>
      <div class="info-row">
        <span class="key">Nominal</span>
        <span style="font-family:'JetBrains Mono',monospace;font-size:18px;font-weight:700;color:var(--indigo);">
          Rp {{ number_format($invoice->nominal, 0, ',', '.') }}
        </span>
      </div>
      <div class="info-row">
        <span class="key">Jatuh Tempo</span>
        <span class="val" style="{{ $invoice->isOverdue() ? 'color:var(--red)' : '' }}">
          {{ $invoice->tgl_jatuh_tempo->format('d F Y') }}
          @if($invoice->isOverdue()) <span style="font-size:10px;">(LEWAT {{ $invoice->tgl_jatuh_tempo->diffInDays() }} HARI)</span>@endif
        </span>
      </div>
      @if($invoice->tgl_bayar)
        <div class="info-row"><span class="key">Tanggal Bayar</span><span class="val" style="color:var(--green)">{{ $invoice->tgl_bayar->format('d F Y') }}</span></div>
      @endif
      @if($invoice->keterangan)
        <div class="info-row"><span class="key">Keterangan</span><span class="val">{{ $invoice->keterangan }}</span></div>
      @endif
    </div>
  </div>

  <!-- Pelanggan Info -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">Data Pelanggan</div>
      <span class="badge badge-{{ $invoice->pelanggan->status }}">{{ strtoupper($invoice->pelanggan->status) }}</span>
    </div>
    <div class="card-body">
      <div class="info-row"><span class="key">Nama</span><span class="val">{{ $invoice->pelanggan->nama }}</span></div>
      <div class="info-row"><span class="key">PPPoE</span><span class="val">{{ $invoice->pelanggan->username_pppoe }}</span></div>
      <div class="info-row"><span class="key">Paket</span><span class="val">{{ $invoice->pelanggan->paket?->nama ?? '-' }}</span></div>
      <div class="info-row"><span class="key">Telepon</span><span class="val">{{ $invoice->pelanggan->phone ?? '-' }}</span></div>
      <div style="margin-top:16px;">
        <a href="{{ route('pelanggan.show', $invoice->pelanggan->id) }}" class="btn btn-ghost btn-sm">
          <i class="fas fa-external-link-alt"></i> Lihat Profil Pelanggan
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Riwayat Pembayaran -->
<div class="card">
  <div class="card-header">
    <div class="card-title">Riwayat Pembayaran</div>
    @if($invoice->status !== 'paid')
      <a href="{{ route('pembayaran.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus"></i> Catat Pembayaran
      </a>
    @endif
  </div>
  <div class="card-body-flush">
    @if($invoice->pembayarans->count() > 0)
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>Tanggal</th><th>Nominal</th><th>Metode</th><th>Dicatat Oleh</th><th>Keterangan</th>
            </tr>
          </thead>
          <tbody>
            @foreach($invoice->pembayarans as $p)
              <tr>
                <td class="mono-mute">{{ $p->tgl_bayar->format('d/m/Y') }}</td>
                <td style="font-family:'JetBrains Mono',monospace;color:var(--green);font-weight:600;">
                  Rp {{ number_format($p->nominal, 0, ',', '.') }}
                </td>
                <td><span class="badge badge-active">{{ strtoupper(str_replace('_', ' ', $p->metode)) }}</span></td>
                <td style="font-size:12px;">{{ $p->user?->name ?? 'Sistem' }}</td>
                <td style="font-size:12px;color:var(--text-3);">{{ $p->keterangan ?? '—' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div style="padding:12px 16px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:8px;font-size:13px;">
        <span style="color:var(--text-3);">Total Dibayar:</span>
        <span style="font-family:'JetBrains Mono',monospace;font-weight:700;color:var(--green);">
          Rp {{ number_format($invoice->pembayarans->sum('nominal'), 0, ',', '.') }}
        </span>
        <span style="color:var(--text-3);">/ Rp {{ number_format($invoice->nominal, 0, ',', '.') }}</span>
      </div>
    @else
      <div class="empty-state">
        <i class="fas fa-receipt"></i>
        <h3>Belum ada pembayaran</h3>
        <p>Invoice ini belum memiliki riwayat pembayaran</p>
      </div>
    @endif
  </div>
</div>
@endsection
