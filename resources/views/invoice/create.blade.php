@extends('layouts.app')
@section('title', 'Buat Invoice')
@section('page-title', 'Buat Invoice')
@section('breadcrumb', 'Invoice / Buat')

@section('content')
<div class="page-header">
  <div class="page-header-title">
    <h1>Buat Invoice Baru</h1>
    <p>Tambahkan tagihan untuk pelanggan</p>
  </div>
  <div class="page-header-actions">
    <a href="{{ route('invoice.index') }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Kembali</a>
  </div>
</div>

<div style="max-width:680px; margin:0 auto;">
  <div class="card">
    <div class="card-header">
      <div class="card-title"><i class="fas fa-file-invoice-dollar" style="color:var(--indigo);margin-right:8px;"></i>Detail Invoice</div>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('invoice.store') }}">
        @csrf
        <div class="form-group">
          <label class="form-label">Pelanggan <span style="color:var(--red)">*</span></label>
          <select name="pelanggan_id" class="form-control" required onchange="document.getElementById('nominal_input_create').value = this.options[this.selectedIndex].getAttribute('data-harga') || '';">
            <option value="" data-harga="">— Pilih Pelanggan —</option>
            @foreach($pelanggans as $p)
              <option value="{{ $p->id }}" data-harga="{{ $p->paket->harga ?? 0 }}" {{ old('pelanggan_id') == $p->id ? 'selected' : '' }}>
                {{ $p->nama }} — {{ $p->username_pppoe }} (Rp {{ number_format($p->paket->harga ?? 0, 0, ',', '.') }})
              </option>
            @endforeach
          </select>
          @error('pelanggan_id')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Periode <span style="color:var(--red)">*</span></label>
            <input type="text" name="periode" class="form-control"
                   value="{{ old('periode', now()->translatedFormat('F Y')) }}"
                   required placeholder="e.g. Juni 2025">
            @error('periode')<div class="form-error">{{ $message }}</div>@enderror
          </div>
          <div class="form-group">
            <label class="form-label">Nominal (Rp) <span style="color:var(--red)">*</span></label>
            <input type="number" id="nominal_input_create" name="nominal" class="form-control form-control-mono"
                   value="{{ old('nominal') }}" required min="0" placeholder="200000">
            @error('nominal')<div class="form-error">{{ $message }}</div>@enderror
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Jatuh Tempo <span style="color:var(--red)">*</span></label>
            <input type="date" name="tgl_jatuh_tempo" class="form-control"
                   value="{{ old('tgl_jatuh_tempo', now()->addDays(10)->format('Y-m-d')) }}" required>
            @error('tgl_jatuh_tempo')<div class="form-error">{{ $message }}</div>@enderror
          </div>
          <div class="form-group">
            <label class="form-label">Keterangan</label>
            <input type="text" name="keterangan" class="form-control"
                   value="{{ old('keterangan') }}" placeholder="Opsional">
          </div>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:8px;">
          <a href="{{ route('invoice.index') }}" class="btn btn-ghost">Batal</a>
          <button type="submit" class="btn btn-primary"><i class="fas fa-file-invoice"></i> Buat Invoice</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
