@extends('layouts.app')

@section('title', 'Tiket & Job Order')

@section('content')
<div class="content-header">
    <div>
        <h1 class="content-title"><i class="fas fa-clipboard-list"></i> Papan Job Order</h1>
        <p class="content-subtitle">Manajemen daftar PSB, Gangguan, dan pekerjaan lapangan lainnya.</p>
    </div>
    @if(auth()->user()->role === 'admin' || auth()->user()->role === 'kasir')
    <a href="{{ route('tickets.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Buat Tiket Baru
    </a>
    @endif
</div>

<!-- Tabs -->
<div class="tabs-container mb-20">
    @if(auth()->user()->role === 'teknisi')
        <a href="{{ route('tickets.index', ['tab' => 'semua']) }}" class="btn {{ $tab == 'semua' ? 'btn-primary' : 'btn-outline' }}" style="color: {{ $tab == 'semua' ? '#fff' : 'var(--text-2)' }}; border-color: {{ $tab == 'semua' ? 'transparent' : 'rgba(255,255,255,0.1)' }};">
            <i class="fas fa-list"></i> Semua Aktif
        </a>
        <a href="{{ route('tickets.index', ['tab' => 'tersedia']) }}" class="btn {{ $tab == 'tersedia' ? 'btn-primary' : 'btn-outline' }}" style="color: {{ $tab == 'tersedia' ? '#fff' : 'var(--text-2)' }}; border-color: {{ $tab == 'tersedia' ? 'transparent' : 'rgba(255,255,255,0.1)' }};">
            <i class="fas fa-hand-paper"></i> Tersedia (Belum Diambil)
        </a>
        <a href="{{ route('tickets.index', ['tab' => 'tugas_saya']) }}" class="btn {{ $tab == 'tugas_saya' ? 'btn-primary' : 'btn-outline' }}" style="color: {{ $tab == 'tugas_saya' ? '#fff' : 'var(--text-2)' }}; border-color: {{ $tab == 'tugas_saya' ? 'transparent' : 'rgba(255,255,255,0.1)' }};">
            <i class="fas fa-hard-hat"></i> Tugas Saya
        </a>
        <a href="{{ route('tickets.index', ['tab' => 'riwayat']) }}" class="btn {{ $tab == 'riwayat' ? 'btn-primary' : 'btn-outline' }}" style="color: {{ $tab == 'riwayat' ? '#fff' : 'var(--text-2)' }}; border-color: {{ $tab == 'riwayat' ? 'transparent' : 'rgba(255,255,255,0.1)' }};">
            <i class="fas fa-check-double"></i> Riwayat Saya
        </a>
    @else
        <a href="{{ route('tickets.index', ['tab' => 'semua']) }}" class="btn {{ $tab == 'semua' ? 'btn-primary' : 'btn-outline' }}" style="color: {{ $tab == 'semua' ? '#fff' : 'var(--text-2)' }}; border-color: {{ $tab == 'semua' ? 'transparent' : 'rgba(255,255,255,0.1)' }};">
            <i class="fas fa-list"></i> Semua Job Order
        </a>
        <a href="{{ route('tickets.index', ['tab' => 'belum_diambil']) }}" class="btn {{ $tab == 'belum_diambil' ? 'btn-primary' : 'btn-outline' }}" style="color: {{ $tab == 'belum_diambil' ? '#fff' : '#ef4444' }}; border-color: {{ $tab == 'belum_diambil' ? 'transparent' : 'rgba(239,68,68,0.3)' }};">
            <i class="fas fa-exclamation-circle"></i> Belum Diambil
        </a>
        <a href="{{ route('tickets.index', ['tab' => 'sedang_dikerjakan']) }}" class="btn {{ $tab == 'sedang_dikerjakan' ? 'btn-primary' : 'btn-outline' }}" style="color: {{ $tab == 'sedang_dikerjakan' ? '#fff' : '#3b82f6' }}; border-color: {{ $tab == 'sedang_dikerjakan' ? 'transparent' : 'rgba(59,130,246,0.3)' }};">
            <i class="fas fa-tools"></i> Sedang Dikerjakan
        </a>
        <a href="{{ route('tickets.index', ['tab' => 'arsip']) }}" class="btn {{ $tab == 'arsip' ? 'btn-primary' : 'btn-outline' }}" style="color: {{ $tab == 'arsip' ? '#fff' : 'var(--text-2)' }}; border-color: {{ $tab == 'arsip' ? 'transparent' : 'rgba(255,255,255,0.1)' }};">
            <i class="fas fa-archive"></i> Riwayat Selesai
        </a>
    @endif
</div>

<!-- Filters -->
<div class="card" style="margin-bottom: 20px;">
    <form action="{{ route('tickets.index') }}" method="GET" class="filter-form d-flex align-items-center gap-15" style="flex-wrap: wrap;">
        <div class="filter-search" style="flex-grow: 1; min-width: 200px;">
            <input type="text" name="search" class="form-control" placeholder="Cari No Tiket, Nama Pelanggan..." value="{{ request('search') }}">
        </div>

        <select name="kategori" class="form-control" style="width: auto;">
            <option value="">Semua Kategori</option>
            <option value="PSB" {{ request('kategori') == 'PSB' ? 'selected' : '' }}>PSB</option>
            <option value="Gangguan" {{ request('kategori') == 'Gangguan' ? 'selected' : '' }}>Gangguan</option>
            <option value="Cabut Modem" {{ request('kategori') == 'Cabut Modem' ? 'selected' : '' }}>Cabut Modem</option>
            <option value="Lainnya" {{ request('kategori') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
        </select>
        
        <select name="status" class="form-control" style="width: auto;">
            <option value="">Semua Status</option>
            <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>⏳ Pending</option>
            <option value="Proses" {{ request('status') == 'Proses' ? 'selected' : '' }}>🛠️ Proses</option>
            <option value="Selesai" {{ request('status') == 'Selesai' ? 'selected' : '' }}>✅ Selesai</option>
        </select>

        <button type="submit" class="btn btn-secondary">Filter</button>
        @if(request()->has('kategori') || request()->has('status') || request()->has('search'))
            <a href="{{ route('tickets.index') }}" class="btn btn-outline" style="color:var(--text-3); text-decoration:none;">Reset</a>
        @endif
    </form>
</div>

<!-- Kanban Style Layout (or grid) -->
<div class="ticket-grid">
    @forelse($tickets as $ticket)
        <div class="card ticket-card" style="border-left: 4px solid 
            @if($ticket->status == 'Pending') #f59e0b 
            @elseif($ticket->status == 'Proses') #3b82f6 
            @else #10b981 
            @endif;">
            <div class="d-flex justify-content-between align-items-start mb-10">
                <div class="badge 
                    @if($ticket->kategori == 'PSB') bg-success 
                    @elseif($ticket->kategori == 'Gangguan') bg-danger 
                    @else bg-secondary @endif">
                    {{ $ticket->kategori }}
                </div>
                <div style="font-size: 12px; color: var(--text-4);">
                    <i class="far fa-clock"></i> {{ $ticket->created_at->diffForHumans() }}
                </div>
            </div>
            
            <h3 style="font-size: 16px; margin: 0 0 5px 0;">
                <a href="{{ route('tickets.show', $ticket->id) }}" style="color: var(--text-1); text-decoration: none;">
                    {{ $ticket->nomor_tiket }}
                </a>
            </h3>
            
            <p style="font-size: 14px; margin: 0 0 15px 0; color: var(--text-2);">
                @if($ticket->pelanggan_id)
                    <i class="fas fa-user"></i> {{ $ticket->pelanggan->nama }}
                @else
                    <i class="fas fa-user-clock"></i> {{ $ticket->nama_pelapor }} <br>
                    <i class="fas fa-phone"></i> {{ $ticket->no_hp }}
                @endif
            </p>

            <div style="background: rgba(255,255,255,0.03); padding: 10px; border-radius: 6px; font-size: 13px; color: var(--text-3); margin-bottom: 15px;">
                <strong>Deskripsi:</strong><br>
                {{ Str::limit($ticket->deskripsi_pekerjaan, 100) }}
            </div>

            <div class="d-flex justify-content-between align-items-center mb-10">
                <div style="font-size: 13px;">
                    @if($ticket->status == 'Pending')
                        <span style="color: #f59e0b; font-weight: 500;"><i class="fas fa-hourglass-half"></i> Pending</span>
                    @elseif($ticket->status == 'Proses')
                        <span style="color: #3b82f6; font-weight: 500;"><i class="fas fa-tools"></i> Diproses</span>
                    @else
                        <span style="color: #10b981; font-weight: 500;"><i class="fas fa-check-circle"></i> Selesai 👍</span>
                    @endif
                </div>
                <div style="font-size: 12px; color: var(--text-3); text-align: right;">
                    @if($ticket->teknisi)
                        <i class="fas fa-hard-hat" style="color: var(--text-4);"></i> {{ $ticket->teknisi->name }}
                    @else
                        <span style="color: var(--text-4);"><i>Belum diambil</i></span>
                    @endif
                </div>
            </div>
            <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-sm btn-outline" style="width: 100%; text-align: center; color: var(--text-2); text-decoration: none;">Lihat Detail Tiket</a>
        </div>
    @empty
        <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
            <i class="fas fa-clipboard-check mb-15" style="font-size: 40px; color: var(--text-4);"></i>
            <h3>Tidak ada tiket ditemukan</h3>
            <p style="color: var(--text-4);">Semua pekerjaan sudah selesai atau belum ada pekerjaan baru.</p>
        </div>
    @endforelse
</div>

<div class="mt-20">
    {{ $tickets->links('pagination::bootstrap-5') }}
</div>

<style>
.ticket-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
}
.ticket-card {
    transition: transform 0.2s, box-shadow 0.2s;
    display: flex;
    flex-direction: column;
}
.ticket-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
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
.bg-danger { background: #ef4444; }
.bg-secondary { background: #6b7280; }

.tabs-container {
    display: flex;
    gap: 10px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    padding-bottom: 10px;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .tabs-container {
        overflow-x: auto;
        white-space: nowrap;
        flex-wrap: nowrap;
        padding-bottom: 15px;
        -webkit-overflow-scrolling: touch;
    }
    .tabs-container::-webkit-scrollbar {
        height: 4px;
    }
    .tabs-container::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,0.2);
        border-radius: 4px;
    }
    .ticket-grid {
        grid-template-columns: 1fr;
    }
    .filter-form select, .filter-form button, .filter-form a {
        flex: 1 1 100%;
    }
    .filter-search {
        flex: 1 1 100%;
    }
}
</style>
@endsection
