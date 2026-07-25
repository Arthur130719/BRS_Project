@extends('layouts.app')

@section('title', 'Tiket & Job Order')

@section('content')
<div class="content-header" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px; margin-bottom:20px;">
    <div>
        <h1 class="content-title"><i class="fas fa-clipboard-list"></i> Papan Job Order</h1>
        <p class="content-subtitle">Manajemen daftar PSB, Gangguan, dan pekerjaan lapangan lainnya.</p>
    </div>
    @if(auth()->user()->role === 'admin' || auth()->user()->role === 'kasir')
    <a href="{{ route('tickets.create') }}" class="btn btn-primary" style="align-self:center; white-space:nowrap;">
        <i class="fas fa-plus"></i> Buat Tiket Baru
    </a>
    @endif
</div>

<!-- Tabs -->
<div style="display:flex; align-items:center; gap:8px; flex-wrap:nowrap; margin-bottom:20px; border-bottom:1px solid rgba(255,255,255,0.07); padding-bottom:14px; overflow-x:auto; -webkit-overflow-scrolling:touch; scrollbar-width:none;" class="hide-scrollbar">
    @if(auth()->user()->role === 'teknisi')
        @php $tabs = [
            'semua'       => ['icon' => 'fa-list',        'label' => 'Semua Aktif'],
            'tersedia'    => ['icon' => 'fa-hand-paper',  'label' => 'Tersedia'],
            'tugas_saya'  => ['icon' => 'fa-hard-hat',    'label' => 'Tugas Saya'],
            'riwayat'     => ['icon' => 'fa-check-double','label' => 'Riwayat'],
        ]; @endphp
    @else
        @php $tabs = [
            'semua'            => ['icon' => 'fa-list',            'label' => 'Semua Job Order'],
            'belum_diambil'    => ['icon' => 'fa-exclamation-circle','label' => 'Belum Diambil',      'color' => '#ef4444'],
            'sedang_dikerjakan'=> ['icon' => 'fa-tools',           'label' => 'Sedang Dikerjakan',   'color' => '#3b82f6'],
            'arsip'            => ['icon' => 'fa-archive',         'label' => 'Riwayat Selesai'],
        ]; @endphp
    @endif

    @foreach($tabs as $key => $cfg)
        @php $active = ($tab == $key); $color = $cfg['color'] ?? null; @endphp
        <a href="{{ route('tickets.index', ['tab' => $key]) }}"
            style="display:inline-flex; align-items:center; gap:7px; padding:7px 16px; border-radius:8px; font-size:13px; font-weight:{{ $active ? '700' : '500' }}; text-decoration:none; transition:all 0.15s;
                {{ $active
                    ? 'background:#3b82f6; color:#fff; box-shadow:0 2px 10px rgba(59,130,246,0.4);'
                    : 'background:rgba(255,255,255,0.05); color:' . ($color ?? 'var(--text-2)') . '; border:1px solid rgba(255,255,255,0.1);'
                }}"
            onmouseover="if(!{{ $active ? 'true' : 'false' }}) this.style.background='rgba(255,255,255,0.1)'"
            onmouseout="if(!{{ $active ? 'true' : 'false' }}) this.style.background='rgba(255,255,255,0.05)'">
            <i class="fas {{ $cfg['icon'] }}" style="font-size:12px;"></i>
            {{ $cfg['label'] }}
        </a>
    @endforeach
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
            <option value="Ganti Password Wifi" {{ request('kategori') == 'Ganti Password Wifi' ? 'selected' : '' }}>Ganti Password Wifi</option>
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
            
            <h3 style="font-size: 16px; margin: 0 0 3px 0;">
                <a href="{{ route('tickets.show', $ticket->id) }}" style="color: var(--text-1); text-decoration: none;">
                    {{ $ticket->nomor_tiket }}
                </a>
            </h3>
            @if($ticket->support_ticket_id)
            <div style="font-size: 12px; font-weight: 600; color: #fbbf24; margin-bottom: 10px; background: rgba(251,191,36,0.12); display: inline-block; padding: 2px 8px; border-radius: 4px; border: 1px solid rgba(251,191,36,0.3);">
                <i class="fas fa-headset"></i> Aduan Pelanggan #{{ $ticket->support_ticket_id }}
            </div>
            @endif
            
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

<div class="mt-20" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
    <div style="font-size: 13px; color: var(--text-3);">
        Menampilkan {{ $tickets->firstItem() }}–{{ $tickets->lastItem() }} dari {{ $tickets->total() }} tiket
    </div>
    <div style="display:flex; gap:6px; align-items:center;">
        {{-- Prev --}}
        @if($tickets->onFirstPage())
            <span style="padding:6px 14px; border-radius:6px; background:rgba(255,255,255,0.04); color:var(--text-4); font-size:13px; cursor:not-allowed;">‹ Sebelumnya</span>
        @else
            <a href="{{ $tickets->previousPageUrl() }}" style="padding:6px 14px; border-radius:6px; background:rgba(255,255,255,0.07); color:var(--text-2); font-size:13px; text-decoration:none; border:1px solid rgba(255,255,255,0.08); transition:background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.12)'" onmouseout="this.style.background='rgba(255,255,255,0.07)'">‹ Sebelumnya</a>
        @endif

        {{-- Page numbers --}}
        @foreach($tickets->getUrlRange(1, $tickets->lastPage()) as $page => $url)
            @if($page == $tickets->currentPage())
                <span style="padding:6px 12px; border-radius:6px; background:var(--primary); color:white; font-size:13px; font-weight:600; min-width:32px; text-align:center;">{{ $page }}</span>
            @else
                <a href="{{ $url }}" style="padding:6px 12px; border-radius:6px; background:rgba(255,255,255,0.07); color:var(--text-2); font-size:13px; text-decoration:none; border:1px solid rgba(255,255,255,0.08); min-width:32px; text-align:center; transition:background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.12)'" onmouseout="this.style.background='rgba(255,255,255,0.07)'">{{ $page }}</a>
            @endif
        @endforeach

        {{-- Next --}}
        @if($tickets->hasMorePages())
            <a href="{{ $tickets->nextPageUrl() }}" style="padding:6px 14px; border-radius:6px; background:rgba(255,255,255,0.07); color:var(--text-2); font-size:13px; text-decoration:none; border:1px solid rgba(255,255,255,0.08); transition:background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.12)'" onmouseout="this.style.background='rgba(255,255,255,0.07)'">Berikutnya ›</a>
        @else
            <span style="padding:6px 14px; border-radius:6px; background:rgba(255,255,255,0.04); color:var(--text-4); font-size:13px; cursor:not-allowed;">Berikutnya ›</span>
        @endif
    </div>
</div>

<style>
.ticket-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
}
.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
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
