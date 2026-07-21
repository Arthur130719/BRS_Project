@extends('layouts.app')
@section('title', 'Edit Invoice')
@section('page-title', 'Edit Invoice')
@section('breadcrumb', 'Invoice / Edit')

@section('content')
<div class="page-header">
  <div class="page-header-title">
    <h1>Edit Invoice</h1>
    <p>{{ $invoice->no_invoice }}</p>
  </div>
  <div class="page-header-actions">
    <a href="{{ route('invoice.show', $invoice->id) }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Kembali</a>
  </div>
</div>

<div style="max-width:680px; margin:0 auto;">
  <div class="card">
    <div class="card-header">
      <div class="card-title">Edit Invoice <span class="mono">{{ $invoice->no_invoice }}</span></div>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('invoice.update', $invoice->id) }}">
        @csrf @method('PUT')
        <div class="form-group">
          <label class="form-label">Pelanggan</label>
          <select name="pelanggan_id" class="form-control" required>
            @foreach($pelanggans as $p)
              <option value="{{ $p->id }}" {{ old('pelanggan_id', $invoice->pelanggan_id) == $p->id ? 'selected' : '' }}>
                {{ $p->nama }} — {{ $p->username_pppoe }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Periode</label>
            <input type="text" name="periode" class="form-control"
                   value="{{ old('periode', $invoice->periode) }}" required>
          </div>
          <div class="form-group">
            <label class="form-label">Nominal (Rp)</label>
            <input type="number" name="nominal" class="form-control form-control-mono"
                   value="{{ old('nominal', $invoice->nominal) }}" required min="0">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Jatuh Tempo</label>
            <input type="date" name="tgl_jatuh_tempo" class="form-control"
                   value="{{ old('tgl_jatuh_tempo', $invoice->tgl_jatuh_tempo->format('Y-m-d')) }}" required>
          </div>
          <div class="form-group">
            <label class="form-label">Status</label>
            <select name="status" class="form-control">
              <option value="unpaid" {{ old('status', $invoice->status) == 'unpaid' ? 'selected' : '' }}>Belum Lunas</option>
              <option value="paid" {{ old('status', $invoice->status) == 'paid' ? 'selected' : '' }}>Lunas</option>
              <option value="partial" {{ old('status', $invoice->status) == 'partial' ? 'selected' : '' }}>Parsial</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Keterangan</label>
          <input type="text" name="keterangan" class="form-control"
                 value="{{ old('keterangan', $invoice->keterangan) }}">
        </div>

        <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:8px;">
          <a href="{{ route('invoice.show', $invoice->id) }}" class="btn btn-ghost">Batal</a>
          <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
