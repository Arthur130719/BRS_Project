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
        <button type="button" class="btn btn-primary" @click="$dispatch('open-create-nas')">
            <i class="fas fa-plus"></i> Tambah NAS
        </button>
        <a href="{{ route('nas.index') }}" class="btn btn-ghost">
            <i class="fas fa-rotate-right"></i> Refresh
        </a>
    </div>
</div>

@if ($errors->any())
    <div style="margin-bottom:24px; padding:16px; background:var(--red-dim); color:var(--red); border:1px solid rgba(239,68,68,0.2); border-radius:var(--radius-lg);">
        <h4 style="margin:0 0 8px 0; font-size:14px; display:flex; align-items:center; gap:8px;">
            <i class="fas fa-exclamation-triangle"></i> Gagal Menyimpan Data
        </h4>
        <ul style="margin:0; padding-left:24px; font-size:13px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- NAS Cards Grid --}}
@if(isset($nasList) && $nasList->count() > 0)
<div class="nas-grid">
    @foreach($nasList as $nas)
    @php
        // Keep initial values for fast paint
        $cpu = $nas->cpu_pct ?? 0;
        $mem = $nas->mem_pct ?? 0;
    @endphp
    <div class="nas-card" x-data="nasCard({{ $nas->id }}, {{ $cpu }}, {{ $mem }}, '{{ $nas->uptime ?? 'N/A' }}', {{ $nas->pelanggans_count ?? $nas->pelanggan_count ?? 0 }}, '{{ $nas->status ?? 'offline' }}')" x-init="startPolling()">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:6px;">
            <div class="nas-kode">{{ $nas->kode }}</div>
            <span class="badge" :class="status === 'online' ? 'badge-online' : 'badge-offline'" x-text="status === 'online' ? 'Online' : 'Offline'"></span>
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
            <span :style="'color:' + getCpuColor()">
                <span x-text="cpu + '%'"></span>
            </span>
        </div>
        <div class="progress-bar-wrap">
            <div class="progress-bar-fill" :class="getCpuClass()" :style="'width:' + Math.min(100, cpu) + '%'"></div>
        </div>

        {{-- Memory --}}
        <div class="progress-label">
            <span>Memory</span>
            <span :style="'color:' + getMemColor()">
                <span x-text="mem + '%'"></span>
            </span>
        </div>
        <div class="progress-bar-wrap">
            <div class="progress-bar-fill" :class="getMemClass()" :style="'width:' + Math.min(100, mem) + '%'"></div>
        </div>

        <div class="nas-footer">
            <div class="nas-pelanggan">
                <strong x-text="activeUsers"></strong> Pelanggan Aktif
            </div>
            <div style="font-size:10px; color:var(--text-4); font-family:'JetBrains Mono', monospace;" title="Uptime">
                ↑ <span x-text="uptime"></span>
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
                        // Initial values for fast paint
                        $cpu = $nas->cpu_pct ?? 0;
                        $mem = $nas->mem_pct ?? 0;
                    @endphp
                    <tr x-data="{ 
                            cpu: {{ $cpu }}, 
                            mem: {{ $mem }}, 
                            uptime: '{{ $nas->uptime ?? 'N/A' }}', 
                            activeUsers: {{ $nas->pelanggans_count ?? $nas->pelanggan_count ?? 0 }}, 
                            status: '{{ $nas->status ?? 'offline' }}',
                            getCpuColor() { return this.cpu >= 85 ? 'var(--red)' : (this.cpu >= 60 ? 'var(--amber)' : 'var(--green)'); },
                            getMemColor() { return this.mem >= 85 ? 'var(--red)' : (this.mem >= 60 ? 'var(--amber)' : 'var(--sky)'); }
                        }" 
                        @nas-updated.window="if ($event.detail.id == {{ $nas->id }}) { 
                            cpu = $event.detail.cpu; 
                            mem = $event.detail.mem; 
                            uptime = $event.detail.uptime; 
                            activeUsers = $event.detail.active_users; 
                            status = $event.detail.status; 
                        }">
                        <td><span class="mono">{{ $nas->kode }}</span></td>
                        <td>
                            <div style="font-weight:500; color:var(--text-1);">{{ $nas->nama }}</div>
                        </td>
                        <td><span class="mono">{{ $nas->ip_address }}</span></td>
                        <td>
                            <span style="font-size:12px; color:var(--text-2);">{{ $nas->model ?? '-' }}</span>
                        </td>
                        <td>
                            <span class="badge" :class="status === 'online' ? 'badge-online' : 'badge-offline'" x-text="status === 'online' ? 'Online' : 'Offline'"></span>
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; gap:8px; min-width:80px;">
                                <div style="flex:1; height:4px; background:var(--bg-elevated); border-radius:100px; overflow:hidden;">
                                    <div :style="'width:' + Math.min(100, cpu) + '%; height:100%; border-radius:100px; background:' + getCpuColor()"></div>
                                </div>
                                <span style="font-size:11px; font-family:'JetBrains Mono',monospace; min-width:32px;" :style="'color:' + getCpuColor()" x-text="cpu + '%'"></span>
                            </div>
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; gap:8px; min-width:80px;">
                                <div style="flex:1; height:4px; background:var(--bg-elevated); border-radius:100px; overflow:hidden;">
                                    <div :style="'width:' + Math.min(100, mem) + '%; height:100%; border-radius:100px; background:' + getMemColor()"></div>
                                </div>
                                <span style="font-size:11px; font-family:'JetBrains Mono',monospace; min-width:32px;" :style="'color:' + getMemColor()" x-text="mem + '%'"></span>
                            </div>
                        </td>
                        <td>
                            <span class="mono-mute" x-text="uptime"></span>
                        </td>
                        <td>
                            <span class="mono-mute" x-text="activeUsers"></span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-ghost btn-xs"
                                @click="editNas = {
                                    id: {{ $nas->id }},
                                    kode: '{{ addslashes($nas->kode) }}',
                                    nama: '{{ addslashes($nas->nama) }}',
                                    ip_address: '{{ addslashes($nas->ip_address) }}',
                                    model: '{{ addslashes($nas->model ?? '') }}',
                                    api_user: '{{ addslashes($nas->api_user ?? '') }}',
                                    api_password: '{{ addslashes($nas->api_password ?? '') }}',
                                    api_port: '{{ $nas->api_port ?? 8728 }}',
                                    lokasi: '{{ addslashes($nas->lokasi ?? '') }}'
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
<template x-teleport="body">
    <div x-show="editOpen" class="modal-overlay" @click.self="editOpen=false" x-cloak>
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-pencil" style="color:var(--indigo);margin-right:8px;"></i>Edit NAS</div>
            <button type="button" class="modal-close" @click="editOpen=false"><i class="fas fa-times"></i></button>
        </div>
        <form :action="`{{ url('nas') }}/${editNas.id}`" method="POST">
            @csrf
            @method('PUT')
            
            @if ($errors->any())
                <div style="padding:12px; background:var(--red-dim); color:var(--red); border-bottom:1px solid rgba(239,68,68,0.2); font-size:13px;">
                    <strong>Gagal menyimpan!</strong> Periksa form:
                    <ul style="margin-top:4px; margin-bottom:0; padding-left:20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

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
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">API Username</label>
                        <input type="text" name="api_user" class="form-control"
                               :value="editNas.api_user" placeholder="Misal: admin">
                    </div>
                    <div class="form-group">
                        <label class="form-label">API Password</label>
                        <input type="password" name="api_password" class="form-control"
                               :value="editNas.api_password" placeholder="Biarkan kosong jika tidak ada">
                    </div>
                    <div class="form-group" style="max-width: 120px;">
                        <label class="form-label">API Port</label>
                        <input type="number" name="api_port" class="form-control form-control-mono"
                               :value="editNas.api_port" placeholder="8728">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Lokasi / Deskripsi</label>
                    <textarea name="lokasi" class="form-control" rows="2"
                              x-text="editNas.lokasi" placeholder="Keterangan atau lokasi perangkat..."></textarea>
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
</template>
</div>

{{-- Create NAS Modal --}}
<div x-data="{ createOpen: false }" @open-create-nas.window="createOpen = true">
    <template x-teleport="body">
        <div x-show="createOpen" class="modal-overlay" @click.self="createOpen=false" x-cloak>
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title"><i class="fas fa-plus" style="color:var(--indigo);margin-right:8px;"></i>Tambah NAS Baru</div>
                <button type="button" class="modal-close" @click="createOpen=false"><i class="fas fa-times"></i></button>
            </div>
            <form action="{{ route('nas.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Kode NAS <span style="color:var(--red);">*</span></label>
                            <input type="text" name="kode" class="form-control form-control-mono" required placeholder="NAS-001">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nama <span style="color:var(--red);">*</span></label>
                            <input type="text" name="nama" class="form-control" required placeholder="Router Pusat">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">IP Address <span style="color:var(--red);">*</span></label>
                            <input type="text" name="ip_address" class="form-control form-control-mono" required placeholder="192.168.1.1">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Model</label>
                            <input type="text" name="model" class="form-control" placeholder="Mikrotik RB1100">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">API Username</label>
                            <input type="text" name="api_user" class="form-control" placeholder="Misal: admin">
                        </div>
                        <div class="form-group">
                            <label class="form-label">API Password</label>
                            <input type="password" name="api_password" class="form-control" placeholder="Kosongkan jika tidak ada">
                        </div>
                        <div class="form-group" style="max-width: 120px;">
                            <label class="form-label">API Port</label>
                            <input type="number" name="api_port" class="form-control form-control-mono" placeholder="8728" value="8728">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Lokasi / Deskripsi</label>
                        <textarea name="lokasi" class="form-control" rows="2" placeholder="Keterangan atau lokasi perangkat..."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="display:flex; justify-content:flex-end; gap:8px;">
                    <button type="button" class="btn btn-ghost" @click="createOpen=false">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan NAS
                    </button>
                </div>
            </form>
        </div>
        </div>
    </template>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('nasCard', (id, initialCpu, initialMem, initialUptime, initialActiveUsers, initialStatus) => ({
            id: id,
            cpu: initialCpu,
            mem: initialMem,
            uptime: initialUptime,
            activeUsers: initialActiveUsers,
            status: initialStatus,
            loading: true,

            getCpuClass() {
                return this.cpu >= 85 ? 'cpu-high' : (this.cpu >= 60 ? 'cpu-mid' : 'cpu-low');
            },
            getMemClass() {
                return this.mem >= 85 ? 'mem-high' : (this.mem >= 60 ? 'mem-mid' : 'mem-low');
            },
            getCpuColor() {
                return this.cpu >= 85 ? 'var(--red)' : (this.cpu >= 60 ? 'var(--amber)' : 'var(--green)');
            },
            getMemColor() {
                return this.mem >= 85 ? 'var(--red)' : (this.mem >= 60 ? 'var(--amber)' : 'var(--sky)');
            },
            startPolling() {
                this.fetchStats();
                setInterval(() => this.fetchStats(), 30000);
            },
            
            async fetchStats() {
                this.loading = true;
                try {
                    const response = await fetch(`/nas/${this.id}/stats`);
                    const data = await response.json();
                    
                    this.cpu = data.cpu;
                    this.mem = data.mem;
                    this.uptime = data.uptime;
                    this.activeUsers = data.active_users;
                    this.status = data.status;
                    
                    // Dispatch to window so the table row can listen
                    this.$dispatch('nas-updated', { id: this.id, ...data });
                } catch (error) {
                    console.error('Failed to fetch NAS stats:', error);
                    this.status = 'offline';
                } finally {
                    this.loading = false;
                }
            }
        }));
    });
</script>
@endsection
