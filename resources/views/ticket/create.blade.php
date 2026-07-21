@extends('layouts.app')

@section('title', 'Buat Tiket Baru')

@section('content')
<div class="content-header">
    <div>
        <a href="{{ route('tickets.index') }}" style="color: var(--text-3); text-decoration: none; margin-bottom: 10px; display: inline-block;">
            <i class="fas fa-arrow-left"></i> Kembali ke Papan Job Order
        </a>
        <h1 class="content-title">Buat Tiket Baru</h1>
    </div>
</div>

<div class="card">
    <form action="{{ route('tickets.store') }}" method="POST">
        @csrf
        
        <div class="form-group mb-20">
            <label>Kategori Pekerjaan *</label>
            <select name="kategori" class="form-control" required>
                <option value="">-- Pilih Kategori --</option>
                <option value="PSB">Pasang Baru (PSB)</option>
                <option value="Gangguan">Gangguan (GGN)</option>
                <option value="Cabut Modem">Cabut Modem</option>
                <option value="Lainnya">Lainnya</option>
            </select>
        </div>

        <div class="form-group mb-20">
            <label>Pelanggan (Pilih jika sudah ada di sistem)</label>
            <select name="pelanggan_id" class="form-control select2">
                <option value="">-- Bukan Pelanggan / Pelanggan Baru --</option>
                @foreach($pelanggans as $p)
                    <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->username_pppoe }})</option>
                @endforeach
            </select>
            <small style="color: var(--text-4);">Jika calon pelanggan baru (PSB), kosongkan ini dan isi nama di bawah.</small>
        </div>

        <div class="grid-2 gap-20">
            <div class="form-group mb-20">
                <label>Nama Pelapor (Jika pelanggan tidak ada di sistem)</label>
                <input type="text" name="nama_pelapor" class="form-control" placeholder="Nama prospek/pelapor">
            </div>
            
            <div class="form-group mb-20">
                <label>No. HP / WA</label>
                <input type="text" name="no_hp" class="form-control" placeholder="Contoh: 0857-XXXX-XXXX">
            </div>
        </div>

        <div class="form-group mb-20">
            <label>Alamat (Wajib untuk eksekusi gangguan / lokasi baru)</label>
            <textarea name="alamat" class="form-control" rows="2" placeholder="Detail alamat lokasi pengerjaan..."></textarea>
            <small style="color: var(--text-4);">Jika ini pelanggan lama, sistem tetap akan merujuk ke alamat utamanya, tapi Anda bisa mengetik patokan spesifik di sini.</small>
        </div>

        <div class="grid-2 gap-20">
            <div class="form-group mb-20">
                <label>Latitude</label>
                <input type="text" name="latitude" class="form-control" placeholder="Contoh: -6.12345678">
            </div>
            
            <div class="form-group mb-20">
                <label>Longitude</label>
                <input type="text" name="longitude" class="form-control" placeholder="Contoh: 106.12345678">
            </div>
        </div>

        <div class="form-group mb-20">
            <label>Deskripsi Kendala / Pekerjaan *</label>
            <textarea name="deskripsi_pekerjaan" class="form-control" rows="4" placeholder="Misal: Modem pingin pindah ke dalam..." required></textarea>
        </div>

        <div class="grid-2 gap-20">
            <div class="form-group mb-20">
                <label>Permintaan Jadwal Eksekusi</label>
                <input type="datetime-local" name="jadwal_kunjungan" class="form-control">
            </div>
            
            <div class="form-group mb-20">
                <label>Tugaskan ke Teknisi</label>
                <select name="teknisi_id" class="form-control">
                    <option value="">-- Bebas (Terbuka untuk Semua Teknisi) --</option>
                    @foreach($teknisis as $t)
                        <option value="{{ $t->id }}">{{ $t->name }} ({{ ucfirst($t->role) }})</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-actions mt-20" style="text-align: right;">
            <button type="submit" class="btn btn-primary">Simpan & Buat Tiket</button>
        </div>
    </form>
</div>

<style>
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; }
@media(max-width: 768px) { .grid-2 { grid-template-columns: 1fr; } }
</style>
@endsection
