@extends('layouts.app')
@section('title', 'Pencarian')
@section('content')

<div class="page-header">
  <div>
    <h1 class="page-title">Hasil Pencarian</h1>
    <p class="page-desc">Menampilkan hasil pencarian untuk: <strong>"{{ $query }}"</strong></p>
  </div>
</div>

@if(empty($query))
<div class="card empty-state">
  <i class="fas fa-magnifying-glass"></i>
  <h3>Mulai Pencarian</h3>
  <p>Ketikkan nama pelanggan, IP, atau invoice pada kolom pencarian di atas.</p>
</div>
@else

  @if($pelanggan->isEmpty() && $invoices->isEmpty())
  <div class="card empty-state">
    <i class="fas fa-box-open"></i>
    <h3>Tidak ada hasil ditemukan</h3>
    <p>Kami tidak dapat menemukan pelanggan atau invoice dengan kata kunci "{{ $query }}".</p>
  </div>
  @endif

  @if($pelanggan->isNotEmpty())
  <div class="card mb-6">
    <div class="card-header">
      <h2 class="card-title"><i class="fas fa-users" style="color: var(--indigo); margin-right: 8px;"></i> Pelanggan</h2>
    </div>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Nama Lengkap</th>
            <th>Username</th>
            <th>IP Static</th>
            <th>No. WA</th>
            <th>Status</th>
            <th class="text-right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @foreach($pelanggan as $p)
          <tr>
            <td><strong>{{ $p->nama }}</strong></td>
            <td class="font-mono text-mute">{{ $p->username_pppoe }}</td>
            <td class="font-mono text-mute">{{ $p->ip_address ?: '-' }}</td>
            <td class="font-mono text-mute">{{ $p->phone ?: '-' }}</td>
            <td>
              @if($p->status === 'aktif') <span class="badge badge-online">Aktif</span>
              @elseif($p->status === 'nonaktif') <span class="badge badge-offline">Nonaktif</span>
              @elseif($p->status === 'isolir') <span class="badge badge-weak">Isolir</span>
              @else <span class="badge badge-manual">{{ ucfirst($p->status) }}</span>
              @endif
            </td>
            <td class="text-right">
              <a href="{{ route('pelanggan.show', $p->id) }}" class="btn btn-sm btn-primary">Detail</a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @endif

  @if($invoices->isNotEmpty())
  <div class="card mb-6">
    <div class="card-header">
      <h2 class="card-title"><i class="fas fa-file-invoice-dollar" style="color: var(--green); margin-right: 8px;"></i> Invoice</h2>
    </div>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>No. Invoice</th>
            <th>Pelanggan</th>
            <th>Bulan</th>
            <th>Total</th>
            <th>Status</th>
            <th class="text-right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @foreach($invoices as $inv)
          <tr>
            <td><strong>{{ $inv->no_invoice }}</strong></td>
            <td>{{ $inv->pelanggan->nama ?? 'Terhapus' }}</td>
            <td>{{ \Carbon\Carbon::parse($inv->periode)->translatedFormat('F Y') }}</td>
            <td class="font-mono">Rp {{ number_format($inv->nominal,0,',','.') }}</td>
            <td>
              @if($inv->status === 'paid') <span class="badge badge-online">Lunas</span>
              @elseif($inv->status === 'unpaid') <span class="badge badge-offline">Belum Bayar</span>
              @else <span class="badge badge-manual">{{ ucfirst($inv->status) }}</span>
              @endif
            </td>
            <td class="text-right">
              <a href="{{ route('invoice.show', $inv->id) }}" class="btn btn-sm btn-primary">Detail</a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @endif

@endif

@endsection
