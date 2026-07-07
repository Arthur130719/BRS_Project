@extends('layouts.app')
@section('title', 'Detail Pembayaran')
@section('page-title', 'Detail Pembayaran')
@section('breadcrumb', 'Pembayaran / Detail')

@section('content')
<div class="page-header">
  <div class="page-header-title">
    <h1>Detail Pembayaran</h1>
    <p>Invoice {{ $pembayaran->invoice->no_invoice }}</p>
  </div>
  <div class="page-header-actions">
    <a href="{{ route('pembayaran.index') }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Kembali</a>
  </div>
</div>

<div style="max-width:680px;margin:0 auto;" class="grid-2">
  <div class="card">
    <div class="card-header"><div class="card-title">Info Pembayaran</div></div>
    <div class="card-body">
      <div class="info-row">
        <span class="key">Nominal</span>
        <span style="font-family:'JetBrains Mono',monospace;font-size:20px;font-weight:700;color:var(--green);">
          Rp {{ number_format($pembayaran->nominal, 0, ',', '.') }}
        </span>
      </div>
      <div class="info-row"><span class="key">Metode</span><span class="val">{{ strtoupper(str_replace('_', ' ', $pembayaran->metode)) }}</span></div>
      @if($pembayaran->nama_bank)
        <div class="info-row"><span class="key">Bank</span><span class="val">{{ $pembayaran->nama_bank }}</span></div>
      @endif
      <div class="info-row"><span class="key">Tanggal Bayar</span><span class="val">{{ $pembayaran->tgl_bayar->format('d F Y') }}</span></div>
      <div class="info-row"><span class="key">Dicatat Oleh</span><span class="val">{{ $pembayaran->user?->name ?? 'Sistem' }}</span></div>
      @if($pembayaran->keterangan)
        <div class="info-row"><span class="key">Keterangan</span><span class="val">{{ $pembayaran->keterangan }}</span></div>
      @endif
      @if($pembayaran->bukti_transfer)
        <div class="info-row">
          <span class="key">Bukti Transfer</span>
          <a href="{{ asset('storage/' . $pembayaran->bukti_transfer) }}" target="_blank" class="btn btn-ghost btn-xs">
            <i class="fas fa-image"></i> Lihat Bukti
          </a>
        </div>
      @endif
      <div class="info-row"><span class="key">Waktu Dicatat</span><span class="val" style="color:var(--text-3);">{{ $pembayaran->created_at->format('d/m/Y H:i') }}</span></div>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><div class="card-title">Invoice Terkait</div></div>
    <div class="card-body">
      <div class="info-row"><span class="key">No. Invoice</span><span class="val">{{ $pembayaran->invoice->no_invoice }}</span></div>
      <div class="info-row"><span class="key">Periode</span><span class="val">{{ $pembayaran->invoice->periode }}</span></div>
      <div class="info-row">
        <span class="key">Total Tagihan</span>
        <span class="val">Rp {{ number_format($pembayaran->invoice->nominal, 0, ',', '.') }}</span>
      </div>
      <div class="info-row">
        <span class="key">Status</span>
        <span class="badge badge-{{ $pembayaran->invoice->status }}">{{ strtoupper($pembayaran->invoice->status) }}</span>
      </div>
      <div class="info-row"><span class="key">Pelanggan</span><span class="val">{{ $pembayaran->invoice->pelanggan->nama }}</span></div>
      <div class="info-row"><span class="key">PPPoE</span><span class="val">{{ $pembayaran->invoice->pelanggan->username_pppoe }}</span></div>
      <div style="margin-top:16px; display:flex; gap:8px;">
        <a href="{{ route('invoice.show', $pembayaran->invoice->id) }}" class="btn btn-ghost btn-sm">
          <i class="fas fa-file-invoice"></i> Lihat Invoice
        </a>
        <a href="{{ route('pelanggan.show', $pembayaran->invoice->pelanggan_id) }}" class="btn btn-ghost btn-sm">
          <i class="fas fa-user"></i> Profil Pelanggan
        </a>
      </div>
    </div>
  </div>
</div>
@endsection
