@extends('layouts.app')
@section('title', 'Edit Pelanggan')
@section('page-title', 'Edit Pelanggan')
@section('breadcrumb', 'Pelanggan / Edit')

@section('content')
<div class="page-header">
  <div class="page-header-title">
    <h1>Edit Pelanggan</h1>
    <p>{{ $pelanggan->nama }} — <span class="mono">{{ $pelanggan->username_pppoe }}</span></p>
  </div>
  <div class="page-header-actions">
    <a href="{{ route('pelanggan.show', $pelanggan->id) }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Kembali</a>
  </div>
</div>

<form method="POST" action="{{ route('pelanggan.update', $pelanggan->id) }}">
  @csrf @method('PUT')
  <div class="grid-2 mb-4">
    <div class="card">
      <div class="card-header">
        <div class="card-title"><i class="fas fa-wifi" style="color:var(--indigo);margin-right:8px;"></i>Akun PPPoE</div>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label class="form-label">Username PPPoE <span style="color:var(--red)">*</span></label>
          <input type="text" name="username_pppoe" class="form-control form-control-mono"
                 value="{{ old('username_pppoe', $pelanggan->username_pppoe) }}" required>
          @error('username_pppoe')<div class="form-error">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
          <label class="form-label">Password PPPoE <span style="color:var(--text-3);font-weight:400;">(kosongkan jika tidak diubah)</span></label>
          <input type="text" name="password_pppoe" class="form-control form-control-mono" placeholder="••••••">
          @error('password_pppoe')<div class="form-error">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
          <label class="form-label">Paket Layanan <span style="color:var(--red)">*</span></label>
          <select name="paket_id" class="form-control" required>
            @foreach($pakets as $paket)
              <option value="{{ $paket->id }}" {{ old('paket_id', $pelanggan->paket_id) == $paket->id ? 'selected' : '' }}>
                {{ $paket->nama }} — Rp {{ number_format($paket->harga, 0, ',', '.') }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">NAS / Router</label>
          <select name="nas_id" class="form-control">
            <option value="">— Tanpa NAS —</option>
            @foreach($nasList as $nas)
              <option value="{{ $nas->id }}" {{ old('nas_id', $pelanggan->nas_id) == $nas->id ? 'selected' : '' }}>
                {{ $nas->kode }} ({{ $nas->ip_address }})
              </option>
            @endforeach
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">OLT</label>
          <select name="olt_id" class="form-control">
            <option value="">— Tanpa OLT —</option>
            @foreach($oltList as $olt)
              <option value="{{ $olt->id }}" {{ old('olt_id', $pelanggan->olt_id) == $olt->id ? 'selected' : '' }}>
                {{ $olt->nama }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Status</label>
          <select name="status" class="form-control">
            <option value="active" {{ $pelanggan->status=='active'?'selected':'' }}>Aktif</option>
            <option value="suspend" {{ $pelanggan->status=='suspend'?'selected':'' }}>Isolir</option>
            <option value="inactive" {{ $pelanggan->status=='inactive'?'selected':'' }}>Nonaktif</option>
          </select>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <div class="card-title"><i class="fas fa-user" style="color:var(--green);margin-right:8px;"></i>Data Pelanggan</div>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label class="form-label">Nama Lengkap <span style="color:var(--red)">*</span></label>
          <input type="text" name="nama" class="form-control"
                 value="{{ old('nama', $pelanggan->nama) }}" required>
          @error('nama')<div class="form-error">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
          <label class="form-label">No. Telepon</label>
          <input type="text" name="phone" class="form-control form-control-mono"
                 value="{{ old('phone', $pelanggan->phone) }}">
        </div>
        <div class="form-group">
          <label class="form-label">Alamat Instalasi</label>
          <textarea name="alamat" class="form-control" rows="3">{{ old('alamat', $pelanggan->alamat) }}</textarea>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Tanggal Aktif</label>
            <input type="date" name="tgl_aktif" class="form-control"
                   value="{{ old('tgl_aktif', $pelanggan->tgl_aktif?->format('Y-m-d')) }}">
          </div>
          <div class="form-group">
            <label class="form-label">Tanggal Expired</label>
            <input type="date" name="expiry" class="form-control"
                   value="{{ old('expiry', $pelanggan->expiry?->format('Y-m-d')) }}">
          </div>
        </div>
      </div>
    </div>
  </div>

  <div style="display:flex; justify-content:flex-end; gap:10px;">
    <a href="{{ route('pelanggan.show', $pelanggan->id) }}" class="btn btn-ghost">Batal</a>
    <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Simpan Perubahan</button>
  </div>
</form>
@endsection
