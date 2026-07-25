@extends('layouts.app')

@section('title', 'Aduan Pelanggan')

@section('content')
<div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-end; flex-wrap: wrap; gap: 15px;">
    <div class="page-title">
        <h1><i class="fas fa-headset" style="color:var(--primary); margin-right:8px;"></i> Aduan Pelanggan</h1>
        <p>Kelola dan pantau tiket bantuan yang dibuat langsung oleh pelanggan.</p>
    </div>
    <div class="tabs-container" style="background: rgba(255,255,255,0.05); padding: 6px; border-radius: 12px; display: inline-flex; gap: 4px; border: 1px solid rgba(255,255,255,0.1);">
        <a href="{{ route('support-tickets.index') }}" 
           style="padding: 8px 20px; border-radius: 8px; color: {{ request('filter') !== 'arsip' ? '#fff' : '#9ca3af' }}; background: {{ request('filter') !== 'arsip' ? 'var(--primary)' : 'transparent' }}; text-decoration: none; font-weight: 600; font-size: 14px; transition: all 0.3s; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-list"></i> Tiket Aktif
        </a>
        <a href="{{ route('support-tickets.index', ['filter' => 'arsip']) }}" 
           style="padding: 8px 20px; border-radius: 8px; color: {{ request('filter') === 'arsip' ? '#fff' : '#9ca3af' }}; background: {{ request('filter') === 'arsip' ? 'var(--primary)' : 'transparent' }}; text-decoration: none; font-weight: 600; font-size: 14px; transition: all 0.3s; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-archive"></i> Arsip Selesai
        </a>
    </div>
</div>

<div style="margin-bottom: 1.5rem; display: flex; justify-content: flex-end;">
    <form method="GET" action="{{ route('support-tickets.index') }}" style="display: flex; gap: 8px; width: 100%; max-width: 400px;">
        @if(request('filter'))
            <input type="hidden" name="filter" value="{{ request('filter') }}">
        @endif
        <input type="text" name="search" class="form-control" placeholder="Cari nama pelanggan atau #ID aduan..." value="{{ request('search') }}" style="border-radius: 8px; flex: 1; padding: 10px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); color: #fff; font-size: 14px;">
        <button type="submit" class="btn btn-primary" style="border-radius: 8px; padding: 10px 20px;">
            <i class="fas fa-search"></i>
        </button>
    </form>
</div>

<div class="card" x-data="{ modalTicket: null, selectedIds: [], selectAll: false }" x-init="$watch('selectAll', val => { selectedIds = val ? {{ json_encode($tickets->pluck('id')->toArray()) }} : [] })">
    
    @if(request('filter') === 'arsip')
    <div class="bulk-actions" x-show="selectedIds.length > 0" style="padding: 12px 16px; background: rgba(239,68,68,0.1); border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;" x-cloak>
        <span style="color: #fca5a5; font-weight: 600;"><span x-text="selectedIds.length"></span> aduan terpilih</span>
        <form action="{{ route('support-tickets.bulk-destroy') }}" method="POST" onsubmit="return confirm('Yakin hapus ' + selectedIds.length + ' aduan secara PERMANEN?');">
            @csrf
            @method('DELETE')
            <template x-for="id in selectedIds">
                <input type="hidden" name="ids[]" :value="id">
            </template>
            <button type="submit" class="btn btn-sm" style="background: #ef4444; color: white; border: none; border-radius: 6px; padding: 6px 12px; font-weight: bold; cursor: pointer;">
                <i class="fas fa-trash-alt"></i> Hapus Terpilih
            </button>
        </form>
    </div>
    @endif

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    @if(request('filter') === 'arsip')
                    <th style="width: 40px; text-align: center;">
                        <input type="checkbox" x-model="selectAll" style="transform: scale(1.2); cursor: pointer;">
                    </th>
                    @endif
                    <th>ID ADUAN</th>
                    <th>TGL DIBUAT</th>
                    <th>PELANGGAN</th>
                    <th>ALAMAT</th>
                    <th>JUDUL MASALAH</th>
                    <th>STATUS</th>
                    <th class="text-right">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $t)
                <tr>
                    @if(request('filter') === 'arsip')
                    <td style="text-align: center;">
                        <input type="checkbox" value="{{ $t->id }}" x-model="selectedIds" style="transform: scale(1.2); cursor: pointer;" number>
                    </td>
                    @endif
                    <td>
                        <span style="font-weight: bold; color: var(--primary);">#{{ $t->id }}</span>
                    </td>
                    <td>
                        <div class="font-medium">{{ $t->created_at->format('d M Y') }}</div>
                        <div class="text-sm text-gray">{{ $t->created_at->format('H:i') }} WIB</div>
                    </td>
                    <td>
                        <div class="font-medium">{{ $t->pelanggan->nama ?? '-' }}</div>
                        <div class="text-sm text-gray">{{ $t->pelanggan->username_pppoe ?? '-' }}</div>
                    </td>
                    <td>
                        <div class="text-sm" style="max-width:200px; white-space:normal;">{{ $t->alamat ?: ($t->pelanggan->alamat ?? '-') }}</div>
                        @if($t->latitude && $t->longitude)
                            <div class="text-sm mt-1">
                                <a href="https://www.google.com/maps/search/?api=1&query={{ $t->latitude }},{{ $t->longitude }}" target="_blank" style="color: var(--primary); text-decoration: underline;">
                                    <i class="fas fa-map-marker-alt"></i> Peta Lokasi
                                </a>
                            </div>
                        @elseif($t->pelanggan && $t->pelanggan->latitude && $t->pelanggan->longitude)
                            <div class="text-sm mt-1">
                                <a href="https://www.google.com/maps/search/?api=1&query={{ $t->pelanggan->latitude }},{{ $t->pelanggan->longitude }}" target="_blank" style="color: var(--primary); text-decoration: underline; opacity: 0.8;">
                                    <i class="fas fa-map-marker-alt"></i> Peta (Pelanggan)
                                </a>
                            </div>
                        @endif
                    </td>
                    <td>
                        <div class="font-medium" style="max-width:250px; white-space:normal;">{{ $t->subject }}</div>
                        <div class="text-sm text-gray mt-1" style="max-width:250px; white-space:normal;">{{ Str::limit($t->deskripsi, 50) }}</div>
                    </td>
                    <td>
                        @if($t->status === 'open')
                            <span class="badge badge-error">Open</span>
                        @elseif($t->status === 'in_progress')
                            <span class="badge badge-warning">In Progress</span>
                        @else
                            <span class="badge badge-success">Resolved</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <div style="display: flex; justify-content: flex-end; gap: 4px;">
                            @if($t->status === 'open')
                            <form action="{{ route('support-tickets.create-job-order', $t->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-primary" title="Buat Job Order">
                                    <i class="fas fa-hammer"></i> Job Order
                                </button>
                            </form>
                            @endif
                            <button type="button" class="btn btn-sm btn-outline" @click="modalTicket = {{ $t->id }}" title="Lihat Detail">
                                <i class="fas fa-eye"></i> Detail
                            </button>
                            
                            @if(request('filter') === 'arsip')
                            <form action="{{ route('support-tickets.destroy', $t->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus riwayat aduan ini secara PERMANEN? Data tidak bisa dikembalikan dan akan hilang dari HP Pelanggan.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm" style="border: 1px solid #ef4444; color: #ef4444; background: transparent;" title="Hapus Permanen">
                                    <i class="fas fa-trash-alt"></i> Hapus
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ request('filter') === 'arsip' ? '8' : '7' }}" class="text-center py-4">Belum ada aduan dari pelanggan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($tickets->hasPages())
    <div style="padding:1rem; border-top:1px solid var(--border);">
        {{ $tickets->links() }}
    </div>
    @endif

    <!-- Modal Detail & Update Status -->
    @foreach($tickets as $t)
    <template x-teleport="body">
    <div x-show="modalTicket === {{ $t->id }}" class="modal-overlay" @click.self="modalTicket = null" x-cloak>
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title"><i class="fas fa-ticket-alt" style="color:var(--primary); margin-right:8px;"></i> Detail Aduan #{{ $t->id }}</span>
                <span class="modal-close" @click="modalTicket = null"><i class="fas fa-times"></i></span>
            </div>
            <div class="modal-body">
                <div style="background:var(--bg-card); padding:1rem; border-radius:8px; margin-bottom:1rem; border:1px solid var(--border);">
                    <div style="display:grid; grid-template-columns: 100px 1fr; gap:0.5rem; margin-bottom:0.5rem;">
                        <strong class="text-sm text-gray">Pelanggan</strong>
                        <div>{{ $t->pelanggan->nama ?? '-' }} ({{ $t->pelanggan->username_pppoe ?? '-' }})</div>
                    </div>
                    <div style="display:grid; grid-template-columns: 100px 1fr; gap:0.5rem; margin-bottom:0.5rem;">
                        <strong class="text-sm text-gray">Alamat</strong>
                        <div>{{ $t->alamat ?: ($t->pelanggan->alamat ?? '-') }}</div>
                    </div>
                    <div style="display:grid; grid-template-columns: 100px 1fr; gap:0.5rem; margin-bottom:0.5rem;">
                        <strong class="text-sm text-gray">Koordinat</strong>
                        <div>
                            @if($t->latitude && $t->longitude)
                                {{ $t->latitude }}, {{ $t->longitude }}
                                <a href="https://www.google.com/maps/search/?api=1&query={{ $t->latitude }},{{ $t->longitude }}" target="_blank" style="margin-left: 8px; color: var(--primary); text-decoration: underline;">
                                    <i class="fas fa-map-marker-alt"></i> Buka Google Maps
                                </a>
                            @elseif($t->pelanggan && $t->pelanggan->latitude && $t->pelanggan->longitude)
                                {{ $t->pelanggan->latitude }}, {{ $t->pelanggan->longitude }}
                                <a href="https://www.google.com/maps/search/?api=1&query={{ $t->pelanggan->latitude }},{{ $t->pelanggan->longitude }}" target="_blank" style="margin-left: 8px; color: var(--primary); text-decoration: underline; opacity: 0.8;">
                                    <i class="fas fa-map-marker-alt"></i> Buka Google Maps (Pelanggan)
                                </a>
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns: 100px 1fr; gap:0.5rem; margin-bottom:0.5rem;">
                        <strong class="text-sm text-gray">Telepon</strong>
                        <div>{{ $t->pelanggan->phone ?? '-' }}</div>
                    </div>
                </div>

                <div style="margin-bottom:1.5rem;">
                    <h4 style="margin-bottom:0.5rem; font-size:1.1rem; font-weight:600;">{{ $t->subject }}</h4>
                    <div style="padding:1rem; background:rgba(0,0,0,0.1); border-radius:8px; white-space:pre-wrap;">{{ $t->deskripsi }}</div>
                </div>

                <form id="status-form-{{ $t->id }}" action="{{ route('support-tickets.status', $t->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Ubah Status Aduan</label>
                        <select name="status" class="form-control" required>
                            <option value="open" {{ $t->status === 'open' ? 'selected' : '' }}>Open (Baru)</option>
                            <option value="in_progress" {{ $t->status === 'in_progress' ? 'selected' : '' }}>In Progress (Sedang Ditangani)</option>
                            <option value="resolved" {{ $t->status === 'resolved' ? 'selected' : '' }}>Resolved (Selesai)</option>
                        </select>
                    </div>
                </form>

                <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:20px; align-items: center;">
                    @if($t->status === 'open')
                    <form action="{{ route('support-tickets.create-job-order', $t->id) }}" method="POST" style="margin:0;">
                        @csrf
                        <button type="submit" class="btn btn-primary"><i class="fas fa-hammer"></i> Buat Job Order</button>
                    </form>
                    @endif
                    
                    <button type="button" class="btn btn-ghost" @click="modalTicket = null">Tutup</button>
                    <button type="submit" class="btn btn-ghost" form="status-form-{{ $t->id }}">Simpan Status</button>
                </div>
            </div>
    </div>
    </template>
    @endforeach
</div>

@endsection
