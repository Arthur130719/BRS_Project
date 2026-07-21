@extends('layouts.app')

@section('title', 'Aduan Pelanggan')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-headset" style="color:var(--primary); margin-right:8px;"></i> Aduan Pelanggan</h1>
        <p>Kelola dan pantau tiket bantuan yang dibuat langsung oleh pelanggan.</p>
    </div>
</div>

<div class="card" x-data="{ modalTicket: null }">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
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
                        <button type="button" class="btn btn-sm btn-outline" @click="modalTicket = {{ $t->id }}" title="Lihat Detail">
                            <i class="fas fa-eye"></i> Detail
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4">Belum ada aduan dari pelanggan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($tickets->hasPages())
    <div style="padding:1rem; border-top:1px solid var(--border);">
        {{ $tickets->links('pagination::tailwind') }}
    </div>
    @endif

    <!-- Modal Detail & Update Status -->
    @foreach($tickets as $t)
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

                <form action="{{ route('support-tickets.status', $t->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Ubah Status Aduan</label>
                        <select name="status" class="form-control" required>
                            <option value="open" {{ $t->status === 'open' ? 'selected' : '' }}>Open (Baru)</option>
                            <option value="in_progress" {{ $t->status === 'in_progress' ? 'selected' : '' }}>In Progress (Sedang Ditangani)</option>
                            <option value="resolved" {{ $t->status === 'resolved' ? 'selected' : '' }}>Resolved (Selesai)</option>
                        </select>
                    </div>
                    <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:20px;">
                        <button type="button" class="btn btn-ghost" @click="modalTicket = null">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>

@endsection
