@extends('layouts.app')
@section('title', 'Paket Layanan')
@section('page-title', 'Paket Layanan')
@section('breadcrumb', 'Pengaturan / Paket')

@section('content')
<div class="page-header">
  <div class="page-header-title">
    <h1>Paket Layanan</h1>
    <p>Kelola paket internet yang tersedia</p>
  </div>
  <div x-data="{ open: false }">
    <button @click="open=true" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Paket</button>
    <template x-teleport="body">
      <div x-show="open" class="modal-overlay" @click.self="open=false" x-cloak>
      <div class="modal">
        <div class="modal-header">
          <span class="modal-title">Tambah Paket Baru</span>
          <span class="modal-close" @click="open=false"><i class="fas fa-times"></i></span>
        </div>
        <form method="POST" action="{{ route('paket.store') }}">
          @csrf
          <div class="modal-body">
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Nama Paket (di Web)</label>
                <input type="text" name="nama" class="form-control" value="{{ old('nama') }}" required placeholder="e.g. Home 20 Mbps">
                @error('nama')<span class="form-error">{{ $message }}</span>@enderror
              </div>
              <div class="form-group">
                <label class="form-label">Nama Profil (di MikroTik)</label>
                <input type="text" name="mikrotik_profile" class="form-control" value="{{ old('mikrotik_profile') }}" placeholder="e.g. PAKET-200">
                @error('mikrotik_profile')<span class="form-error">{{ $message }}</span>@enderror
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Kecepatan Download (Mbps)</label>
                <input type="number" name="kecepatan_down" class="form-control" value="{{ old('kecepatan_down') }}" required min="1">
              </div>
              <div class="form-group">
                <label class="form-label">Kecepatan Upload (Mbps)</label>
                <input type="number" name="kecepatan_up" class="form-control" value="{{ old('kecepatan_up') }}" required min="1">
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Harga (Rp)</label>
              <input type="number" name="harga" class="form-control form-control-mono" value="{{ old('harga') }}" required min="0" placeholder="200000">
              @error('harga')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
              <label class="form-label">Deskripsi (opsional)</label>
              <input type="text" name="deskripsi" class="form-control" value="{{ old('deskripsi') }}" placeholder="Keterangan tambahan">
            </div>
            <div class="form-group" style="margin-top: 15px;">
              <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                <input type="checkbox" name="tampil_di_web" value="1" checked style="width:18px;height:18px;cursor:pointer;">
                <span style="font-weight:600; color:var(--text-1);">Tampilkan di Web Publik?</span>
              </label>
              <div style="font-size:12px; color:var(--text-4); margin-left:26px;">
                Jika dicentang, paket ini akan terlihat di halaman pendaftaran pelanggan. Jangan dicentang jika ini adalah paket khusus (Private).
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" @click="open=false">Batal</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Simpan</button>
          </div>
        </form>
      </div>
    </template>
  </div>
</div>

<div class="grid-3 mb-4">
  @forelse($pakets as $paket)
  <div class="card" x-data="{ editOpen: false }">
    <div class="card-header">
      <div>
        <div class="card-title">
          {{ $paket->nama }}
          @if(!$paket->tampil_di_web)
          <span style="font-size:10px; background:var(--text-4); color:white; padding:2px 6px; border-radius:4px; margin-left:6px; vertical-align:middle;">Hidden</span>
          @endif
        </div>
        <div class="card-subtitle">{{ $paket->pelanggans_count }} pelanggan aktif</div>
      </div>
      <span class="badge {{ $paket->is_active ? 'badge-active' : 'badge-inactive' }}">
        {{ $paket->is_active ? 'Aktif' : 'Nonaktif' }}
      </span>
    </div>
    <div class="card-body">
      <div style="font-family:'JetBrains Mono',monospace; font-size:22px; font-weight:700; color:var(--text-1); margin-bottom:4px;">
        {{ 'Rp ' . number_format($paket->harga, 0, ',', '.') }}
      </div>
      <div style="font-size:12px; color:var(--text-3); margin-bottom:16px;">/bulan</div>
      <div class="info-row">
        <span class="key">Download</span>
        <span class="val">{{ $paket->kecepatan_down }} Mbps</span>
      </div>
      <div class="info-row">
        <span class="key">Upload</span>
        <span class="val">{{ $paket->kecepatan_up }} Mbps</span>
      </div>
      @if($paket->deskripsi)
      <div style="font-size:12px; color:var(--text-3); margin-top:10px;">{{ $paket->deskripsi }}</div>
      @endif
      <div style="display:flex; gap:8px; margin-top:14px;">
        <button @click="editOpen=true" class="btn btn-ghost btn-sm" style="flex:1;"><i class="fas fa-pen"></i> Edit</button>
        @if($paket->pelanggans_count == 0)
        <form method="POST" action="{{ route('paket.destroy', $paket->id) }}" onsubmit="return confirm('Hapus paket {{ $paket->nama }}?')">
          @csrf @method('DELETE')
          <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
        </form>
        @endif
      </div>
    </div>

    {{-- Edit Modal --}}
    <template x-teleport="body">
      <div x-show="editOpen" class="modal-overlay" @click.self="editOpen=false" x-cloak>
      <div class="modal">
        <div class="modal-header">
          <span class="modal-title">Edit Paket — {{ $paket->nama }}</span>
          <span class="modal-close" @click="editOpen=false"><i class="fas fa-times"></i></span>
        </div>
        <form method="POST" action="{{ route('paket.update', $paket->id) }}">
          @csrf @method('PUT')
          <div class="modal-body">
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Nama Paket (Web)</label>
                <input type="text" name="nama" class="form-control" value="{{ $paket->nama }}" required>
              </div>
              <div class="form-group">
                <label class="form-label">Nama Profil (MikroTik)</label>
                <input type="text" name="mikrotik_profile" class="form-control" value="{{ $paket->mikrotik_profile }}">
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Download (Mbps)</label>
                <input type="number" name="kecepatan_down" class="form-control" value="{{ $paket->kecepatan_down }}" required min="1">
              </div>
              <div class="form-group">
                <label class="form-label">Upload (Mbps)</label>
                <input type="number" name="kecepatan_up" class="form-control" value="{{ $paket->kecepatan_up }}" required min="1">
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Harga (Rp)</label>
              <input type="number" name="harga" class="form-control form-control-mono" value="{{ $paket->harga }}" required min="0">
            </div>
            <div class="form-group">
              <label class="form-label">Deskripsi</label>
              <input type="text" name="deskripsi" class="form-control" value="{{ $paket->deskripsi }}">
            </div>
            <div class="form-group">
              <label class="form-label">Status</label>
              <select name="is_active" class="form-control">
                <option value="1" {{ $paket->is_active?'selected':'' }}>Aktif</option>
                <option value="0" {{ !$paket->is_active?'selected':'' }}>Nonaktif</option>
              </select>
            </div>
            <div class="form-group" style="margin-top: 15px;">
              <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                <input type="checkbox" name="tampil_di_web" value="1" {{ $paket->tampil_di_web ? 'checked' : '' }} style="width:18px;height:18px;cursor:pointer;">
                <span style="font-weight:600; color:var(--text-1);">Tampilkan di Web Publik?</span>
              </label>
              <div style="font-size:12px; color:var(--text-4); margin-left:26px;">
                Jika dicentang, paket ini akan terlihat di halaman pendaftaran pelanggan. Jangan dicentang jika ini adalah paket khusus (Private).
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" @click="editOpen=false">Batal</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Simpan</button>
          </div>
        </form>
      </div>
    </div>
    </template>
  </div>
  @empty
    <div class="card" style="grid-column:1/-1;">
      <div class="empty-state"><i class="fas fa-boxes-stacked"></i><h3>Belum ada paket</h3></div>
    </div>
  @endforelse
</div>
@endsection
