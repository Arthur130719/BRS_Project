@extends('layouts.app')
@section('title', 'NAS / Router')
@section('page-title', 'NAS / Router')
@section('breadcrumb', 'Jaringan / NAS Router')

@section('content')

<style>
.nas-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

@media (max-width: 1100px) { .nas-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 700px)  { .nas-grid { grid-template-columns: 1fr; } }

.nas-card {
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 18px;
    transition: border-color var(--transition), transform var(--transition);
    position: relative;
    overflow: hidden;
}

.nas-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    background: linear-gradient(90deg, var(--indigo), transparent);
    border-radius: var(--radius-lg) var(--radius-lg) 0 0;
}

.nas-card:hover {
    border-color: var(--border-light);
    transform: translateY(-1px);
}

.nas-kode {
    font-family: 'JetBrains Mono', monospace;
    font-size: 22px;
    font-weight: 700;
    color: #a5b4fc;
    margin-bottom: 2px;
    letter-spacing: -0.5px;
}

.nas-name {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-1);
    margin-bottom: 2px;
}

.nas-ip {
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px;
    color: var(--text-3);
}

.nas-model-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    background: var(--indigo-dim);
    color: #a5b4fc;
    border: 1px solid rgba(99,102,241,0.2);
    margin-top: 6px;
}

.nas-divider {
    border: none;
    border-top: 1px solid var(--border);
    margin: 12px 0;
}

/* Progress bar */
.progress-label {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px;
}

.progress-label span:first-child {
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-4);
}

.progress-label span:last-child {
    font-size: 11px;
    font-family: 'JetBrains Mono', monospace;
    font-weight: 600;
}

.progress-bar-wrap {
    height: 5px;
    background: var(--bg-elevated);
    border-radius: 100px;
    overflow: hidden;
    margin-bottom: 10px;
}

.progress-bar-fill {
    height: 100%;
    border-radius: 100px;
    transition: width 0.6s ease;
}

.progress-bar-fill.cpu-low    { background: var(--green); }
.progress-bar-fill.cpu-mid    { background: var(--amber); }
.progress-bar-fill.cpu-high   { background: var(--red); }
.progress-bar-fill.mem-low    { background: var(--sky); }
.progress-bar-fill.mem-mid    { background: var(--amber); }
.progress-bar-fill.mem-high   { background: var(--red); }

.nas-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 8px;
}

.nas-pelanggan {
    font-size: 11px;
    color: var(--text-3);
}

.nas-pelanggan strong {
    font-family: 'JetBrains Mono', monospace;
    color: var(--text-1);
}
</style>

<div class="page-header">
    <div class="page-header-title">
        <h1>NAS / Router</h1>
        <p>Manajemen perangkat Network Access Server dan router jaringan</p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('nas.index') }}" class="btn btn-ghost">
            <i class="fas fa-rotate-right"></i> Refresh
        </a>
    </div>
</div>

{{-- NAS Cards Grid --}}
@if(isset($nasList) && $nasList->count() > 0)
<div class="nas-grid">
    @foreach($nasList as $nas)
    @php
        $cpu = $nas->cpu_percent ?? 0;
        $mem = $nas->memory_percent ?? 0;
        $cpuClass = $cpu >= 85 ? 'cpu-high' : ($cpu >= 60 ? 'cpu-mid' : 'cpu-low');
        $memClass = $mem >= 85 ? 'mem-high' : ($mem >= 60 ? 'mem-mid' : 'mem-low');
        $cpuColor = $cpu >= 85 ? 'var(--red)' : ($cpu >= 60 ? 'var(--amber)' : 'var(--green)');
        $memColor = $mem >= 85 ? 'var(--red)' : ($mem >= 60 ? 'var(--amber)' : 'var(--sky)');
    @endphp
    <div class="nas-card">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:6px;">
            <div class="nas-kode">{{ $nas->kode }}</div>
            @if(($nas->status ?? 'online') === 'online')
                <span class="badge badge-online">Online</span>
            @else
                <span class="badge badge-offline">Offline</span>
            @endif
        </div>
        <div class="nas-name">{{ $nas->nama }}</div>
        <div class="nas-ip">{{ $nas->ip_address }}</div>
        @if($nas->model)
            <div class="nas-model-badge">{{ $nas->model }}</div>
        @endif

        <hr class="nas-divider">

        {{-- CPU --}}
        <div class="progress-label">
            <span>CPU</span>
            <span style="color:{{ $cpuColor }};">{{ $cpu }}%</span>
        </div>
        <div class="progress-bar-wrap">
            <div class="progress-bar-fill {{ $cpuClass }}" style="width:{{ min(100, $cpu) }}%;"></div>
        </div>

        {{-- Memory --}}
        <div class="progress-label">
            <span>Memory</span>
            <span style="color:{{ $memColor }};">{{ $mem }}%</span>
        </div>
        <div class="progress-bar-wrap">
            <div class="progress-bar-fill {{ $memClass }}" style="width:{{ min(100, $mem) }}%;"></div>
        </div>

        <div class="nas-footer">
            <div class="nas-pelanggan">
                <strong>{{ $nas->pelanggans_count ?? $nas->pelanggan_count ?? 0 }}</strong> Pelanggan
            </div>
            <div style="font-size:10px; color:var(--text-4); font-family:'JetBrains Mono', monospace;">
                ↑ {{ $nas->uptime ?? 'N/A' }}
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="card" style="margin-bottom:24px;">
    <div class="card-body" style="text-align:center; padding:40px;">
        <i class="fas fa-network-wired" style="font-size:36px; color:var(--border-light); display:block; margin-bottom:12px;"></i>
        <div style="font-weight:600; color:var(--text-3); margin-bottom:4px;">Tidak Ada NAS Terdaftar</div>
        <div style="font-size:12px; color:var(--text-4);">Belum ada perangkat NAS yang dikonfigurasi pada sistem.</div>
    </div>
</div>
@endif

{{-- NAS Table --}}
<div x-data="{ editOpen: false, editNas: {} }">
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Tabel NAS / Router</div>
            <div class="card-subtitle">Detail lengkap semua perangkat NAS</div>
        </div>
        <form method="GET" action="{{ route('nas.index') }}" class="toolbar-search">
            <i class="fas fa-search"></i>
            <input type="text" name="search" placeholder="Cari NAS..." value="{{ request('search') }}">
        </form>
    </div>

    <div class="card-body-flush">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>IP Address</th>
                        <th>Model</th>
                        <th>Status</th>
                        <th>CPU</th>
                        <th>Memory</th>
                        <th>Uptime</th>
                        <th>Pelanggan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($nasList ?? [] as $nas)
                    @php
                        $cpu = $nas->cpu_percent ?? 0;
                        $mem = $nas->memory_percent ?? 0;
                        $cpuColor = $cpu >= 85 ? 'var(--red)' : ($cpu >= 60 ? 'var(--amber)' : 'var(--green)');
                        $memColor = $mem >= 85 ? 'var(--red)' : ($mem >= 60 ? 'var(--amber)' : 'var(--sky)');
                    @endphp
                    <tr>
                        <td><span class="mono">{{ $nas->kode }}</span></td>
                        <td>
                            <div style="font-weight:500; color:var(--text-1);">{{ $nas->nama }}</div>
                        </td>
                        <td><span class="mono">{{ $nas->ip_address }}</span></td>
                        <td>
                            <span style="font-size:12px; color:var(--text-2);">{{ $nas->model ?? '-' }}</span>
                        </td>
                        <td>
                            @if(($nas->status ?? 'online') === 'online')
                                <span class="badge badge-online">Online</span>
                            @else
                                <span class="badge badge-offline">Offline</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; gap:8px; min-width:80px;">
                                <div style="flex:1; height:4px; background:var(--bg-elevated); border-radius:100px; overflow:hidden;">
                                    <div style="width:{{ min(100,$cpu) }}%; height:100%; background:{{ $cpuColor }}; border-radius:100px;"></div>
                                </div>
                                <span style="font-size:11px; font-family:'JetBrains Mono',monospace; color:{{ $cpuColor }}; min-width:32px;">{{ $cpu }}%</span>
                            </div>
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; gap:8px; min-width:80px;">
                                <div style="flex:1; height:4px; background:var(--bg-elevated); border-radius:100px; overflow:hidden;">
                                    <div style="width:{{ min(100,$mem) }}%; height:100%; background:{{ $memColor }}; border-radius:100px;"></div>
                                </div>
                                <span style="font-size:11px; font-family:'JetBrains Mono',monospace; color:{{ $memColor }}; min-width:32px;">{{ $mem }}%</span>
                            </div>
                        </td>
                        <td>
                            <span class="mono-mute">{{ $nas->uptime ?? 'N/A' }}</span>
                        </td>
                        <td>
                            <span class="mono-mute">{{ $nas->pelanggans_count ?? $nas->pelanggan_count ?? 0 }}</span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-ghost btn-xs"
                                @click="editNas = {
                                    id: {{ $nas->id }},
                                    kode: '{{ addslashes($nas->kode) }}',
                                    nama: '{{ addslashes($nas->nama) }}',
                                    ip_address: '{{ addslashes($nas->ip_address) }}',
                                    model: '{{ addslashes($nas->model ?? '') }}',
                                    secret: '{{ addslashes($nas->secret ?? '') }}',
                                    deskripsi: '{{ addslashes($nas->deskripsi ?? '') }}'
                                }; editOpen = true">
                                <i class="fas fa-pencil"></i> Edit
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10">
                            <div style="text-align:center; padding:48px 20px; color:var(--text-4);">
                                <i class="fas fa-network-wired" style="font-size:36px; margin-bottom:12px; display:block; color:var(--border-light);"></i>
                                <div style="font-weight:600; color:var(--text-3); margin-bottom:4px;">Tidak Ada NAS</div>
                                <div style="font-size:12px;">Belum ada perangkat NAS yang terdaftar.</div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(isset($nasList) && method_exists($nasList, 'hasPages') && $nasList->hasPages())
    <div style="padding:12px 16px; border-top:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
        <span class="mono-mute">Menampilkan {{ $nasList->firstItem() }}–{{ $nasList->lastItem() }} dari {{ $nasList->total() }} NAS</span>
        <div style="display:flex; gap:4px;">
            @if($nasList->onFirstPage())
                <span class="btn btn-ghost btn-xs" style="opacity:0.4; cursor:not-allowed;"><i class="fas fa-chevron-left"></i></span>
            @else
                <a href="{{ $nasList->previousPageUrl() }}" class="btn btn-ghost btn-xs"><i class="fas fa-chevron-left"></i></a>
            @endif
            @foreach($nasList->getUrlRange(max(1,$nasList->currentPage()-2), min($nasList->lastPage(),$nasList->currentPage()+2)) as $page => $url)
                <a href="{{ $url }}" class="btn btn-xs {{ $page == $nasList->currentPage() ? 'btn-primary' : 'btn-ghost' }}">{{ $page }}</a>
            @endforeach
            @if($nasList->hasMorePages())
                <a href="{{ $nasList->nextPageUrl() }}" class="btn btn-ghost btn-xs"><i class="fas fa-chevron-right"></i></a>
            @else
                <span class="btn btn-ghost btn-xs" style="opacity:0.4; cursor:not-allowed;"><i class="fas fa-chevron-right"></i></span>
            @endif
        </div>
    </div>
    @endif
</div>

{{-- Edit NAS Modal --}}
<div x-show="editOpen" class="modal-overlay" @click.self="editOpen=false" x-cloak>
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-pencil" style="color:var(--indigo);margin-right:8px;"></i>Edit NAS</div>
            <button type="button" class="modal-close" @click="editOpen=false"><i class="fas fa-times"></i></button>
        </div>
        <form :action="`{{ url('nas') }}/${editNas.id}`" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Kode NAS</label>
                        <input type="text" name="kode" class="form-control form-control-mono"
                               :value="editNas.kode" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nama</label>
                        <input type="text" name="nama" class="form-control"
                               :value="editNas.nama" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">IP Address</label>
                        <input type="text" name="ip_address" class="form-control form-control-mono"
                               :value="editNas.ip_address" required placeholder="192.168.1.1">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Model</label>
                        <input type="text" name="model" class="form-control"
                               :value="editNas.model" placeholder="Mikrotik RB3011">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">RADIUS Secret</label>
                    <input type="text" name="secret" class="form-control form-control-mono"
                           :value="editNas.secret" placeholder="shared_secret">
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="2"
                              x-text="editNas.deskripsi" placeholder="Keterangan perangkat..."></textarea>
                </div>
            </div>
            <div class="modal-footer" style="display:flex; justify-content:flex-end; gap:8px;">
                <button type="button" class="btn btn-ghost" @click="editOpen=false">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
</div>
@endsection
