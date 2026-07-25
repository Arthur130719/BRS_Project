@extends('layouts.app')
@section('title', 'Manajemen Pengguna')
@section('page-title', 'Pengguna Sistem')
@section('breadcrumb', 'Sistem / Pengguna')

@section('content')
<div class="page-header">
  <div class="page-header-title">
    <h1>Manajemen Pengguna</h1>
    <p>Kelola akun admin, kasir, dan teknisi sistem</p>
  </div>
  <div class="page-header-actions" x-data="{ open: false }">
    <button @click="open=true" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Pengguna</button>
    <template x-teleport="body">
    <div x-show="open" class="modal-overlay" @click.self="open=false" x-cloak>
      <div class="modal">
        <div class="modal-header">
          <span class="modal-title">Tambah Pengguna Baru</span>
          <span class="modal-close" @click="open=false"><i class="fas fa-times"></i></span>
        </div>
        <form method="POST" action="{{ route('pengguna.store') }}">
          @csrf
          <div class="modal-body">
            <div class="form-group">
              <label class="form-label">Nama Lengkap</label>
              <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="Nama pengguna">
              @error('name')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control form-control-mono" value="{{ old('email') }}" required placeholder="email@netcore.id">
              @error('email')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required minlength="8" placeholder="Min. 8 karakter">
                @error('password')<span class="form-error">{{ $message }}</span>@enderror
              </div>
              <div class="form-group">
                <label class="form-label">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" class="form-control" required placeholder="Ulangi password">
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Role</label>
                <select name="role" class="form-control" required>
                  <option value="admin" {{ old('role')=='admin'?'selected':'' }}>Admin</option>
                  <option value="kasir" {{ old('role','kasir')=='kasir'?'selected':'' }}>Kasir</option>
                  <option value="teknisi" {{ old('role')=='teknisi'?'selected':'' }}>Teknisi</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Status</label>
                <select name="is_active" class="form-control">
                  <option value="1">Aktif</option>
                  <option value="0">Nonaktif</option>
                </select>
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

<div class="card">
  <div class="card-body-flush">
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Nama</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Dibuat</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($pengguna as $user)
          <tr x-data="{ editOpen: false }">
            <td class="mono-mute">{{ $loop->iteration }}</td>
            <td>
              <div style="display:flex; align-items:center; gap:10px;">
                <div class="user-avatar {{ $user->role }}" style="width:30px;height:30px;font-size:11px;">
                  {{ strtoupper(substr($user->name,0,1)) }}
                </div>
                <span style="font-weight:500;">{{ $user->name }}</span>
                @if($user->id === auth()->id())
                  <span style="font-size:10px;color:var(--text-3);">(Anda)</span>
                @endif
              </div>
            </td>
            <td><span class="mono">{{ $user->email }}</span></td>
            <td>
              <span class="user-role {{ $user->role }}">{{ strtoupper($user->role) }}</span>
            </td>
            <td>
              @if($user->is_active)
                <span class="badge badge-active">Aktif</span>
              @else
                <span class="badge badge-inactive">Nonaktif</span>
              @endif
            </td>
            <td class="mono-mute">{{ $user->created_at->format('d/m/Y') }}</td>
            <td>
              <div style="display:flex; gap:6px;">
                <button @click="editOpen=true" class="btn btn-ghost btn-xs"><i class="fas fa-pen"></i> Edit</button>
                @if($user->id !== auth()->id())
                <form method="POST" action="{{ route('pengguna.destroy', $user->id) }}" onsubmit="return confirm('Hapus pengguna {{ $user->name }}?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-xs"><i class="fas fa-trash"></i></button>
                </form>
                @endif
              </div>

              {{-- Edit Modal --}}
              <template x-teleport="body">
              <div x-show="editOpen" class="modal-overlay" @click.self="editOpen=false" x-cloak>
                <div class="modal">
                  <div class="modal-header">
                    <span class="modal-title">Edit Pengguna — {{ $user->name }}</span>
                    <span class="modal-close" @click="editOpen=false"><i class="fas fa-times"></i></span>
                  </div>
                  <form method="POST" action="{{ route('pengguna.update', $user->id) }}">
                    @csrf @method('PUT')
                    <div class="modal-body">
                      <div class="form-group">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                      </div>
                      <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control form-control-mono" value="{{ $user->email }}" required>
                      </div>
                      <div class="form-row">
                        <div class="form-group">
                          <label class="form-label">Password Baru <span style="color:var(--text-4)">(kosongkan jika tidak diubah)</span></label>
                          <input type="password" name="password" class="form-control" minlength="8" placeholder="••••••••">
                        </div>
                        <div class="form-group">
                          <label class="form-label">Konfirmasi</label>
                          <input type="password" name="password_confirmation" class="form-control" placeholder="••••••••">
                        </div>
                      </div>
                      <div class="form-row">
                        <div class="form-group">
                          <label class="form-label">Role</label>
                          <select name="role" class="form-control">
                            <option value="admin" {{ $user->role=='admin'?'selected':'' }}>Admin</option>
                            <option value="kasir" {{ $user->role=='kasir'?'selected':'' }}>Kasir</option>
                            <option value="teknisi" {{ $user->role=='teknisi'?'selected':'' }}>Teknisi</option>
                          </select>
                        </div>
                        <div class="form-group">
                          <label class="form-label">Status</label>
                          <select name="is_active" class="form-control">
                            <option value="1" {{ $user->is_active?'selected':'' }}>Aktif</option>
                            <option value="0" {{ !$user->is_active?'selected':'' }}>Nonaktif</option>
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-ghost" @click="editOpen=false">Batal</button>
                      <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Simpan</button>
                    </div>
                  </form>
                </div>
              </template>
            </tr>
          @empty
            <tr><td colspan="7"><div class="empty-state"><i class="fas fa-users-slash"></i><h3>Belum ada pengguna</h3></div></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
