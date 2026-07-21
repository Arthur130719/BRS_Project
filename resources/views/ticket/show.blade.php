@extends('layouts.app')

@section('title', 'Detail Job Order: ' . $ticket->nomor_tiket)

@section('content')
<div class="content-header">
    <div>
        <a href="{{ route('tickets.index') }}" style="color: var(--text-3); text-decoration: none; margin-bottom: 10px; display: inline-block;">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <h1 class="content-title">
            Detail Tiket: {{ $ticket->nomor_tiket }} 
            <span class="badge 
                @if($ticket->status == 'Pending') bg-warning 
                @elseif($ticket->status == 'Proses') bg-primary 
                @else bg-success @endif" style="font-size: 14px; margin-left: 10px;">
                {{ $ticket->status }}
            </span>
        </h1>
    </div>
</div>

<div class="grid-layout">
    <!-- Informasi Tiket -->
    <div class="card">
        <h3><i class="fas fa-info-circle"></i> Informasi Pekerjaan</h3>
        <hr style="border-color: rgba(255,255,255,0.05); margin: 15px 0;">
        
        <table class="table" style="width: 100%;">
            <tr>
                <td style="color: var(--text-4); width: 150px;">Kategori</td>
                <td><strong>{{ $ticket->kategori }}</strong></td>
            </tr>
            <tr>
                <td style="color: var(--text-4);">Dilaporkan Oleh</td>
                <td>
                    @if($ticket->pelanggan_id)
                        <a href="{{ route('pelanggan.show', $ticket->pelanggan_id) }}" style="color: #60a5fa; text-decoration: none;">{{ $ticket->pelanggan->nama }}</a>
                    @else
                        {{ $ticket->nama_pelapor }} (Prospek/Bukan Pelanggan)
                    @endif
                </td>
            </tr>
            <tr>
                <td style="color: var(--text-4);">No HP / WA</td>
                <td>{{ $ticket->no_hp ?: ($ticket->pelanggan ? $ticket->pelanggan->no_wa : '-') }}</td>
            </tr>
            <tr>
                <td style="color: var(--text-4);">Alamat / Patokan</td>
                <td>
                    {{ $ticket->alamat ?: ($ticket->pelanggan ? $ticket->pelanggan->alamat : '-') }}
                    @if($ticket->alamat && $ticket->pelanggan)
                        <br><small style="color: var(--text-4);">Alamat Pelanggan Asli: {{ $ticket->pelanggan->alamat }}</small>
                    @endif
                </td>
            </tr>
            <tr>
                <td style="color: var(--text-4);">Koordinat Lokasi</td>
                <td>
                    @if($ticket->latitude && $ticket->longitude)
                        <span class="mono">{{ $ticket->latitude }}, {{ $ticket->longitude }}</span>
                        <a href="https://www.google.com/maps/search/?api=1&query={{ $ticket->latitude }},{{ $ticket->longitude }}" target="_blank" style="margin-left: 8px; color: #60a5fa; text-decoration: underline;">
                            <i class="fas fa-map-marker-alt"></i> Buka Google Maps
                        </a>
                    @elseif($ticket->pelanggan && $ticket->pelanggan->latitude && $ticket->pelanggan->longitude)
                        <span class="mono">{{ $ticket->pelanggan->latitude }}, {{ $ticket->pelanggan->longitude }}</span>
                        <a href="https://www.google.com/maps/search/?api=1&query={{ $ticket->pelanggan->latitude }},{{ $ticket->pelanggan->longitude }}" target="_blank" style="margin-left: 8px; color: #60a5fa; text-decoration: underline;">
                            <i class="fas fa-map-marker-alt"></i> Buka Google Maps (Pelanggan)
                        </a>
                    @else
                        -
                    @endif
                </td>
            </tr>
            <tr>
                <td style="color: var(--text-4);">Tgl Dibuat</td>
                <td>{{ $ticket->created_at->format('d M Y H:i') }}</td>
            </tr>
            <tr>
                <td style="color: var(--text-4);">Jadwal Kunjungan</td>
                <td>
                    @if($ticket->jadwal_kunjungan)
                        <span style="color: #f59e0b;"><i class="far fa-calendar-alt"></i> {{ \Carbon\Carbon::parse($ticket->jadwal_kunjungan)->format('d M Y H:i') }}</span>
                    @else
                        -
                    @endif
                </td>
            </tr>
            <tr>
                <td style="color: var(--text-4);">Teknisi Ditugaskan</td>
                <td>
                    @if($ticket->teknisi)
                        {{ $ticket->teknisi->name }}
                        @if($ticket->nama_partner)
                            <br><small style="color: var(--text-4);"><i class="fas fa-user-friends"></i> Partner: {{ $ticket->nama_partner }}</small>
                        @endif
                    @else
                        <span style="color: #ef4444;">Belum ada teknisi</span>
                    @endif
                </td>
            </tr>
        </table>
        
        <div style="background: rgba(255,255,255,0.03); padding: 15px; border-radius: 8px; margin-top: 15px;">
            <p style="color: var(--text-4); margin-top:0;"><strong>Deskripsi Kendala/Pekerjaan:</strong></p>
            <p style="margin-bottom:0;">{{ $ticket->deskripsi_pekerjaan }}</p>
        </div>
    </div>

    <!-- Update Status & Laporan -->
    <div class="card">
        <h3><i class="fas fa-edit"></i> Update Status & Laporan Teknisi</h3>
        <hr style="border-color: rgba(255,255,255,0.05); margin: 15px 0;">
        
        <form action="{{ route('tickets.update', $ticket->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <input type="hidden" name="kategori" value="{{ $ticket->kategori }}">
            <input type="hidden" name="pelanggan_id" value="{{ $ticket->pelanggan_id }}">
            <input type="hidden" name="nama_pelapor" value="{{ $ticket->nama_pelapor }}">
            <input type="hidden" name="no_hp" value="{{ $ticket->no_hp }}">
            <input type="hidden" name="alamat" value="{{ $ticket->alamat }}">
            <input type="hidden" name="latitude" value="{{ $ticket->latitude }}">
            <input type="hidden" name="longitude" value="{{ $ticket->longitude }}">
            <input type="hidden" name="deskripsi_pekerjaan" value="{{ $ticket->deskripsi_pekerjaan }}">
            <input type="hidden" name="jadwal_kunjungan" value="{{ $ticket->jadwal_kunjungan }}">
            
            <div class="form-group mb-20">
                <label>Ubah Status Pekerjaan</label>
                <select name="status" class="form-control" id="statusSelect">
                    <option value="Pending" {{ $ticket->status == 'Pending' ? 'selected' : '' }}>⏳ Pending</option>
                    <option value="Proses" {{ $ticket->status == 'Proses' ? 'selected' : '' }}>🛠️ Proses / Sedang Dikerjakan</option>
                    <option value="Selesai" {{ $ticket->status == 'Selesai' ? 'selected' : '' }}>✅ Selesai</option>
                </select>
            </div>

            @if(auth()->user()->role !== 'teknisi')
            <div class="form-group mb-20">
                <label>Ubah Teknisi (Admin/Kasir Only)</label>
                <select name="teknisi_id" class="form-control">
                    <option value="">-- Bebas (Terbuka untuk Semua Teknisi) --</option>
                    @foreach($teknisis as $t)
                        <option value="{{ $t->id }}" {{ $ticket->teknisi_id == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            @else
                @if(!$ticket->teknisi_id)
                    <div class="form-group mb-20" style="background: rgba(59, 130, 246, 0.1); padding: 15px; border-radius: 8px; border: 1px solid rgba(59, 130, 246, 0.3);">
                        <label style="color: #3b82f6;"><i class="fas fa-hand-paper"></i> Ambil Pekerjaan Ini</label>
                        <p style="font-size: 13px; color: var(--text-3); margin-top: 5px;">Belum ada teknisi yang mengambil job ini. Centang di bawah untuk mengkonfirmasi bahwa Anda yang akan mengeksekusinya.</p>
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin-top: 10px;">
                            <input type="checkbox" name="teknisi_id" value="{{ auth()->id() }}" style="width: 20px; height: 20px;">
                            <span>Ya, saya yang akan menangani job ini.</span>
                        </label>
                    </div>
                @else
                    <input type="hidden" name="teknisi_id" value="{{ $ticket->teknisi_id }}">
                    <div class="form-group mb-20">
                        <label>Teknisi</label>
                        <input type="text" class="form-control" value="{{ $ticket->teknisi->name }} ({{ $ticket->teknisi_id == auth()->id() ? 'Anda' : 'Rekan Anda' }})" disabled>
                    </div>
                @endif
            @endif

            <div class="form-group mb-20">
                <label>Nama Partner (Opsional)</label>
                <input type="text" name="nama_partner" class="form-control" placeholder="Kosongkan jika kerja sendiri, isi nama jika berdua..." value="{{ $ticket->nama_partner }}">
            </div>

            <div class="form-group mb-20" id="alatSection" style="display: {{ $ticket->status == 'Selesai' ? 'block' : 'none' }};">
                <label style="color: #10b981;"><i class="fas fa-tools"></i> Laporan Penggunaan Alat (Wajib diisi jika Selesai)</label>
                <p style="font-size: 12px; color: var(--text-4);">Contoh: Kabel 50m, 1 Modem ZTE, 1 Splitter, 1 Patchcord</p>
                <textarea name="penggunaan_alat" class="form-control" rows="5" placeholder="Tulis rincian alat yang dipakai di sini...">{{ $ticket->penggunaan_alat }}</textarea>
            </div>

            <div style="text-align: right;">
                <button type="submit" class="btn btn-primary">Simpan Pembaruan</button>
            </div>
        </form>
    </div>
</div>

@if(auth()->user()->role !== 'teknisi')
<form action="{{ route('tickets.destroy', $ticket->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus tiket ini?');" style="margin-top: 20px; text-align: right;">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-outline" style="color: #ef4444; border-color: rgba(239, 68, 68, 0.2);">
        <i class="fas fa-trash"></i> Hapus Tiket
    </button>
</form>
@endif

<style>
.grid-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
@media(max-width: 992px) {
    .grid-layout { grid-template-columns: 1fr; }
}
.table td {
    padding: 10px 0;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}
.badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    color: white;
}
.bg-success { background: #10b981; }
.bg-primary { background: #3b82f6; }
.bg-warning { background: #f59e0b; color: #1e293b; }
</style>

<script>
document.getElementById('statusSelect').addEventListener('change', function() {
    if(this.value === 'Selesai') {
        document.getElementById('alatSection').style.display = 'block';
    } else {
        document.getElementById('alatSection').style.display = 'none';
    }
});
</script>
@endsection
