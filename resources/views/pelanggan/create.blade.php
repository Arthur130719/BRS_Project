@extends('layouts.app')
@section('title', 'Tambah Pelanggan')
@section('page-title', 'Tambah Pelanggan')
@section('breadcrumb', 'Pelanggan / Tambah')

@section('content')
<div class="page-header">
  <div class="page-header-title">
    <h1>Tambah Pelanggan Baru</h1>
    <p>Daftarkan pelanggan baru ke dalam sistem</p>
  </div>
  <div class="page-header-actions">
    <a href="{{ route('pelanggan.index') }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Kembali</a>
  </div>
</div>

<form method="POST" action="{{ route('pelanggan.store') }}">
  @csrf
  <div class="grid-2 mb-4">
    <!-- Kiri: Data PPPoE -->
    <div class="card">
      <div class="card-header">
        <div class="card-title"><i class="fas fa-wifi" style="color:var(--indigo);margin-right:8px;"></i>Akun PPPoE</div>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label class="form-label">Username PPPoE <span style="color:var(--red)">*</span></label>
          <input type="text" name="username_pppoe" class="form-control form-control-mono"
                 value="{{ old('username_pppoe') }}" required placeholder="nama@netcore">
          @error('username_pppoe')<div class="form-error">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
          <label class="form-label">Password PPPoE <span style="color:var(--red)">*</span></label>
          <input type="text" name="password_pppoe" class="form-control form-control-mono"
                 value="{{ old('password_pppoe') }}" required placeholder="password">
          @error('password_pppoe')<div class="form-error">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
          <label class="form-label">Paket Layanan <span style="color:var(--red)">*</span></label>
          <select name="paket_id" class="form-control" required>
            <option value="">— Pilih Paket —</option>
            @foreach($pakets as $paket)
              <option value="{{ $paket->id }}" {{ old('paket_id') == $paket->id ? 'selected' : '' }}>
                {{ $paket->nama }} — Rp {{ number_format($paket->harga, 0, ',', '.') }}
              </option>
            @endforeach
          </select>
          @error('paket_id')<div class="form-error">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
          <label class="form-label">NAS / Router</label>
          <select name="nas_id" class="form-control">
            <option value="">— Pilih NAS —</option>
            @foreach($nasList as $nas)
              <option value="{{ $nas->id }}" {{ old('nas_id') == $nas->id ? 'selected' : '' }}>
                {{ $nas->kode }} ({{ $nas->ip_address }})
              </option>
            @endforeach
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">OLT</label>
          <select name="olt_id" class="form-control">
            <option value="">— Pilih OLT —</option>
            @foreach($oltList as $olt)
              <option value="{{ $olt->id }}" {{ old('olt_id') == $olt->id ? 'selected' : '' }}>
                {{ $olt->nama }} ({{ $olt->ip_address }})
              </option>
            @endforeach
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">IP Pool</label>
          <select name="ip_pool" class="form-control">
            <option value="pool-home" {{ old('ip_pool')=='pool-home'?'selected':'' }}>pool-home (10.10.0.0/16)</option>
            <option value="pool-bisnis" {{ old('ip_pool')=='pool-bisnis'?'selected':'' }}>pool-bisnis (172.16.0.0/16)</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Kanan: Data Pelanggan -->
    <div class="card">
      <div class="card-header">
        <div class="card-title"><i class="fas fa-user" style="color:var(--green);margin-right:8px;"></i>Data Pelanggan</div>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label class="form-label">Nama Lengkap <span style="color:var(--red)">*</span></label>
          <input type="text" name="nama" class="form-control"
                 value="{{ old('nama') }}" required placeholder="Nama lengkap pelanggan">
          @error('nama')<div class="form-error">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
          <label class="form-label">No. Telepon</label>
          <input type="text" name="phone" class="form-control form-control-mono"
                 value="{{ old('phone') }}" placeholder="08xx-xxxx-xxxx">
          @error('phone')<div class="form-error">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
          <label class="form-label">Alamat Instalasi</label>
          <textarea name="alamat" class="form-control" rows="3"
                    placeholder="Jl. Contoh No.1, Kel. ..., Kec. ...">{{ old('alamat') }}</textarea>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Latitude</label>
            <input type="text" name="latitude" class="form-control" value="{{ old('latitude') }}" placeholder="-6.12345678">
            @error('latitude')<div class="form-error">{{ $message }}</div>@enderror
          </div>
          <div class="form-group">
            <label class="form-label">Longitude</label>
            <input type="text" name="longitude" class="form-control" value="{{ old('longitude') }}" placeholder="106.12345678">
            @error('longitude')<div class="form-error">{{ $message }}</div>@enderror
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Tanggal Aktif</label>
            <input type="date" name="tgl_aktif" class="form-control" value="{{ old('tgl_aktif', now()->format('Y-m-d')) }}">
          </div>
          <div class="form-group">
            <label class="form-label">Tanggal Expired</label>
            <input type="date" name="expiry" class="form-control" value="{{ old('expiry', now()->addMonth()->format('Y-m-d')) }}">
          </div>
        </div>

        <div style="margin-top: 8px; padding: 12px; background: rgba(99,102,241,0.06); border: 1px solid rgba(99,102,241,0.2); border-radius: 8px; font-size: 12px; color: var(--text-3);">
          <i class="fas fa-circle-info" style="color:var(--indigo);margin-right:6px;"></i>
          Serial Number ONU dapat diisi setelah instalasi melalui halaman detail pelanggan.
        </div>
      </div>
    </div>
  </div>

  <div style="display:flex; justify-content:flex-end; gap:10px;">
    <a href="{{ route('pelanggan.index') }}" class="btn btn-ghost">Batal</a>
    <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Simpan & Aktifkan</button>
  </div>
</form>
@endsection
