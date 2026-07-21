@extends('layouts.app')

@section('title', 'Permohonan Pemasangan')

@section('content')
<div class="page-header">
    <div class="page-title-wrap">
        <h1 class="page-title">Permohonan Pemasangan Baru</h1>
        <p class="page-subtitle">Daftar calon pelanggan yang mendaftar melalui website.</p>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger">
    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
</div>
@endif

<div class="card mb-6">
    <div class="card-header">
        <h3 class="card-title">Daftar Antrean Permohonan</h3>
    </div>
    
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Waktu Daftar</th>
                    <th>Nama Calon Pelanggan</th>
                    <th>No. HP / WhatsApp</th>
                    <th>Pilihan Paket</th>
                    <th>Alamat Lengkap</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($permohonans as $item)
                <tr>
                    <td>
                        <div class="mono text-mute">{{ $item->created_at->format('d M Y') }}</div>
                        <div class="mono text-mute" style="font-size:10px">{{ $item->created_at->format('H:i') }}</div>
                    </td>
                    <td>
                        <div style="font-weight: 600; color: var(--text-1);">{{ $item->nama }}</div>
                    </td>
                    <td>
                        <div class="mono"><i class="fab fa-whatsapp" style="color: #25D366; margin-right:4px;"></i> {{ $item->phone }}</div>
                    </td>
                    <td>
                        <span class="badge badge-auto">{{ $item->paket->nama ?? '-' }}</span>
                    </td>
                    <td>
                        <div style="max-width: 250px; white-space: normal; line-height: 1.4; font-size: 12px; color: var(--text-2);">
                            {{ $item->alamat }}
                            @if($item->latitude && $item->longitude)
                                <div style="margin-top: 4px;">
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ $item->latitude }},{{ $item->longitude }}" target="_blank" style="color: #60a5fa; text-decoration: underline; font-size: 11px;">
                                        <i class="fas fa-map-marker-alt"></i> Peta Lokasi ({{ $item->latitude }}, {{ $item->longitude }})
                                    </a>
                                </div>
                            @endif
                        </div>
                    </td>
                    <td class="text-right">
                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                            <form action="{{ route('permohonan.accept', $item->id) }}" method="POST" onsubmit="return confirm('Terima permohonan ini dan buat Job Order untuk Teknisi?');">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="fas fa-check"></i> Terima & Buat Job Order
                                </button>
                            </form>
                            <form action="{{ route('permohonan.reject', $item->id) }}" method="POST" onsubmit="return confirm('Tolak dan hapus permohonan ini secara permanen?');">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm" style="padding: 6px 10px;" title="Tolak">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <h3>Belum Ada Permohonan Baru</h3>
                            <p>Daftar prospek dari website akan muncul di sini.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($permohonans->hasPages())
    <div class="card-footer" style="padding: 10px 20px; border-top: 1px solid var(--border);">
        {{ $permohonans->links() }}
    </div>
    @endif
</div>
@endsection
