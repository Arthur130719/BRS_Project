@extends('layouts.app')
@section('title', 'Manajemen Pelanggan')
@section('page-title', 'Pelanggan')
@section('breadcrumb', 'Manajemen / Pelanggan')

@section('content')

{{-- ═══════════════════════════════════════════════
     PAGE HEADER
═══════════════════════════════════════════════ --}}
<div class="page-header">
    <div class="page-header-title">
        <h1><i class="fas fa-users" style="color:var(--indigo);margin-right:8px;"></i>Manajemen Pelanggan</h1>
        <p>Kelola seluruh data pelanggan, status layanan, dan paket internet</p>
    </div>
    <div class="page-header-actions">
        @if(auth()->user()->hasRole(['admin', 'kasir']))
        
        {{-- BULK GENERATE INVOICE --}}
        <div x-data="{ openBulkInvoice: false }" style="display:inline-block; margin-right: 8px;">
            <button type="button" @click="openBulkInvoice = true" class="btn btn-primary" id="btnBulkInvoice" style="display:none; background:var(--sky); border-color:var(--sky);">
                <i class="fas fa-file-invoice-dollar"></i> Buat Tagihan (<span id="bulkCount">0</span>)
            </button>
            <template x-teleport="body">
                <div x-show="openBulkInvoice" class="modal-overlay" @click.self="openBulkInvoice = false" style="display:none;" x-cloak>
                    <div class="modal" @click.stop>
                        <div class="modal-header">
                            <span class="modal-title"><i class="fas fa-file-invoice-dollar" style="color:var(--sky);margin-right:8px;"></i>Buat Tagihan Massal</span>
                            <button class="modal-close" @click="openBulkInvoice = false"><i class="fas fa-xmark"></i></button>
                        </div>
                        <form method="POST" action="{{ route('invoice.bulkGenerateSelected') }}" id="bulkInvoiceForm">
                            @csrf
                            <div id="hiddenBulkInputs"></div>
                            <div class="modal-body">
                                <div style="padding:12px; background:var(--sky-dim); color:var(--sky); border:1px solid rgba(14,165,233,0.2); border-radius:6px; font-size:12px; margin-bottom: 15px;">
                                    <i class="fas fa-info-circle" style="margin-right:4px;"></i>
                                    Tagihan akan dibuat untuk <strong><span id="bulkCountModal">0</span> pelanggan</strong> yang dicentang. Pelanggan yang sudah memiliki tagihan Unpaid atau tidak punya paket akan dilewati otomatis.
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Periode Tagihan <span style="color:var(--red)">*</span></label>
                                    <input type="text" name="periode" class="form-control" placeholder="Contoh: Agustus 2026" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Tanggal Jatuh Tempo <span style="color:var(--red)">*</span></label>
                                    <input type="date" name="tgl_jatuh_tempo" class="form-control" required>
                                </div>
                            </div>
                            <div class="modal-footer" style="display:flex; justify-content:flex-end; gap:8px;">
                                <button type="button" class="btn btn-ghost" @click="openBulkInvoice = false">Batal</button>
                                <button type="submit" class="btn btn-primary" style="background:var(--sky); border-color:var(--sky);">
                                    <i class="fas fa-check"></i> Proses Tagihan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </template>
        </div>

        {{-- BULK DELETE --}}
        <div x-data="{ openBulkDelete: false }" style="display:inline-block; margin-right: 8px;">
            <button type="button" @click="openBulkDelete = true" class="btn btn-danger" id="btnBulkDelete" style="display:none;">
                <i class="fas fa-trash-alt"></i> Hapus (<span id="bulkDeleteCount">0</span>)
            </button>
            <template x-teleport="body">
                <div x-show="openBulkDelete" class="modal-overlay" @click.self="openBulkDelete = false" style="display:none;" x-cloak>
                    <div class="modal" @click.stop>
                        <div class="modal-header">
                            <span class="modal-title"><i class="fas fa-exclamation-triangle" style="color:var(--red);margin-right:8px;"></i>Konfirmasi Hapus Massal</span>
                            <button class="modal-close" @click="openBulkDelete = false"><i class="fas fa-xmark"></i></button>
                        </div>
                        <form method="POST" action="{{ route('pelanggan.bulkDestroySelected') }}" id="bulkDeleteForm">
                            @csrf
                            <div id="hiddenBulkDeleteInputs"></div>
                            <div class="modal-body">
                                <div style="padding:12px; background:rgba(239,68,68,0.1); color:var(--red); border:1px solid rgba(239,68,68,0.2); border-radius:6px; font-size:14px; margin-bottom: 15px;">
                                    <strong>Peringatan Berbahaya!</strong><br><br>
                                    Anda akan menghapus <strong><span id="bulkDeleteCountModal">0</span> pelanggan</strong> secara permanen.
                                    Tindakan ini juga akan menghapus akun PPPoE mereka dari MikroTik. Data yang dihapus tidak dapat dikembalikan.
                                </div>
                                <p>Apakah Anda benar-benar yakin ingin melanjutkan?</p>
                            </div>
                            <div class="modal-footer" style="display:flex; justify-content:flex-end; gap:8px;">
                                <button type="button" class="btn btn-ghost" @click="openBulkDelete = false">Batal</button>
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash-alt"></i> Ya, Hapus Permanen
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </template>
        </div>

        <div x-data="{ openImport: false }" style="display:inline-block; margin-right: 8px;">
            <button @click="openImport = true" class="btn btn-ghost">
                <i class="fas fa-file-import"></i> Import .rsc
            </button>
            <template x-teleport="body">
                <div x-show="openImport" class="modal-overlay" @click.self="openImport = false" style="display:none;" x-cloak>
                    <div class="modal" @click.stop>
                        <div class="modal-header">
                            <span class="modal-title"><i class="fas fa-file-import" style="color:var(--indigo);margin-right:8px;"></i>Import Data .rsc Mikrotik</span>
                            <button class="modal-close" @click="openImport = false"><i class="fas fa-xmark"></i></button>
                        </div>
                        <form method="POST" action="{{ route('pelanggan.importRsc') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-body">
                                <div class="form-group">
                                    <label class="form-label">Router NAS (Opsional)</label>
                                    <select name="nas_id" class="form-control">
                                        <option value="">-- Deteksi Otomatis dari .rsc --</option>
                                        @foreach(\App\Models\Nas::all() as $nas)
                                            <option value="{{ $nas->id }}">{{ $nas->nama }} ({{ $nas->ip_address }})</option>
                                        @endforeach
                                    </select>
                                    <small style="color:var(--text-4); display:block; margin-top:4px;">Pilih Router HANYA JIKA nama (System Identity) router di file .rsc berbeda dengan yang ada di web.</small>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">File .rsc Backup <span style="color:var(--red)">*</span></label>
                                    <input type="file" name="rsc_file" class="form-control" accept=".rsc,.txt" required>
                                    <small style="color:var(--text-4); display:block; margin-top:4px;">Upload file <code>.rsc</code> dari hasil export Mikrotik.</small>
                                </div>
                                <div style="padding:12px; background:var(--sky-dim); color:var(--sky); border:1px solid rgba(14,165,233,0.2); border-radius:6px; font-size:12px;">
                                    <i class="fas fa-info-circle" style="margin-right:4px;"></i>
                                    <strong>Tips:</strong> Sistem hanya akan mengimport profil yang formatnya <code>add name="..." password="..."</code>.
                                </div>
                            </div>
                            <div class="modal-footer" style="display:flex; justify-content:flex-end; gap:8px;">
                                <button type="button" class="btn btn-ghost" @click="openImport = false">Batal</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload"></i> Proses Import
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </template>
        </div>

        <div x-data="{ open: false }" style="display:inline-block">
            <button @click="open = true" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Pelanggan
            </button>

            {{-- ── MODAL TAMBAH PELANGGAN ── --}}
            <template x-teleport="body">
                <div x-show="open"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="modal-overlay"
                     @click.self="open = false"
                 style="display:none;">
                <div class="modal modal-lg" @click.stop>
                    <div class="modal-header">
                        <span class="modal-title"><i class="fas fa-user-plus" style="color:var(--indigo);margin-right:8px;"></i>Tambah Pelanggan Baru</span>
                        <button class="modal-close" @click="open = false"><i class="fas fa-xmark"></i></button>
                    </div>
                    <form method="POST" action="{{ route('pelanggan.store') }}">
                        @csrf
                        <div class="modal-body">

                            {{-- Credentials --}}
                            <div style="font-size:11px;font-weight:600;letter-spacing:0.5px;text-transform:uppercase;color:var(--text-4);margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid var(--border);">
                                Kredensial PPPoE
                            </div>
                            <div class="form-row" style="margin-bottom:14px;">
                                <div class="form-group" style="margin-bottom:0;">
                                    <label class="form-label">Username PPPoE <span style="color:var(--red)">*</span></label>
                                    <input type="text"
                                           name="username_pppoe"
                                           class="form-control form-control-mono"
                                           value="{{ old('username_pppoe') }}"
                                           placeholder="user@isp.net"
                                           autocomplete="off">
                                    @if($errors->has('username_pppoe'))
                                        <div class="form-error">{{ $errors->first('username_pppoe') }}</div>
                                    @endif
                                </div>
                                <div class="form-group" style="margin-bottom:0;">
                                    <label class="form-label">Password PPPoE <span style="color:var(--red)">*</span></label>
                                    <input type="password"
                                           name="password_pppoe"
                                           class="form-control form-control-mono"
                                           value="{{ old('password_pppoe') }}"
                                           placeholder="Min. 3 karakter"
                                           autocomplete="new-password">
                                    @if($errors->has('password_pppoe'))
                                        <div class="form-error">{{ $errors->first('password_pppoe') }}</div>
                                    @endif
                                </div>
                            </div>

                            {{-- Identity --}}
                            <div style="font-size:11px;font-weight:600;letter-spacing:0.5px;text-transform:uppercase;color:var(--text-4);margin-bottom:10px;margin-top:6px;padding-bottom:6px;border-bottom:1px solid var(--border);">
                                Identitas Pelanggan
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nama Lengkap <span style="color:var(--red)">*</span></label>
                                <input type="text"
                                       name="nama"
                                       class="form-control"
                                       value="{{ old('nama') }}"
                                       placeholder="Nama lengkap pelanggan">
                                @if($errors->has('nama'))
                                    <div class="form-error">{{ $errors->first('nama') }}</div>
                                @endif
                            </div>
                            <div class="form-row" style="margin-bottom:14px;">
                                <div class="form-group" style="margin-bottom:0;">
                                    <label class="form-label">No. Telepon</label>
                                    <input type="text"
                                           name="phone"
                                           class="form-control form-control-mono"
                                           value="{{ old('phone') }}"
                                           placeholder="Utama (08x...)">
                                    @if($errors->has('phone'))
                                        <div class="form-error">{{ $errors->first('phone') }}</div>
                                    @endif
                                    <input type="text"
                                           name="phone_2"
                                           class="form-control form-control-mono"
                                           style="margin-top: 6px;"
                                           value="{{ old('phone_2') }}"
                                           placeholder="Alt. (08x...)">
                                    @if($errors->has('phone_2'))
                                        <div class="form-error">{{ $errors->first('phone_2') }}</div>
                                    @endif
                                </div>
                                <div class="form-group" style="margin-bottom:0;">
                                    <label class="form-label">Paket Layanan <span style="color:var(--red)">*</span></label>
                                    <select name="paket_id" class="form-control">
                                        <option value="">— Pilih Paket —</option>
                                        @foreach($pakets as $paket)
                                            <option value="{{ $paket->id }}" {{ old('paket_id') == $paket->id ? 'selected' : '' }}>
                                                {{ $paket->nama }} — Rp {{ number_format($paket->harga, 0, ',', '.') }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('paket_id'))
                                        <div class="form-error">{{ $errors->first('paket_id') }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Alamat</label>
                                <textarea name="alamat"
                                          class="form-control"
                                          rows="2"
                                          placeholder="Alamat lengkap pelanggan">{{ old('alamat') }}</textarea>
                                @if($errors->has('alamat'))
                                    <div class="form-error">{{ $errors->first('alamat') }}</div>
                                @endif
                            </div>

                            {{-- Network --}}
                            <div style="font-size:11px;font-weight:600;letter-spacing:0.5px;text-transform:uppercase;color:var(--text-4);margin-bottom:10px;margin-top:6px;padding-bottom:6px;border-bottom:1px solid var(--border);">
                                Konfigurasi Jaringan
                            </div>
                            <div class="form-row" style="margin-bottom:0;">
                                <div class="form-group" style="margin-bottom:0;">
                                    <label class="form-label">NAS / Router</label>
                                    <select name="nas_id" class="form-control">
                                        <option value="">— Pilih NAS —</option>
                                        @php $nasList = \App\Models\Nas::where('status','online')->get(); @endphp
                                        @foreach($nasList as $nas)
                                            <option value="{{ $nas->id }}" {{ old('nas_id') == $nas->id ? 'selected' : '' }}>
                                                {{ $nas->nama }} ({{ $nas->ip_address }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('nas_id'))
                                        <div class="form-error">{{ $errors->first('nas_id') }}</div>
                                    @endif
                                </div>
                                <div class="form-group" style="margin-bottom:0;">
                                    <label class="form-label">OLT</label>
                                    <select name="olt_id" class="form-control">
                                        <option value="">— Pilih OLT —</option>
                                        @php $oltList = \App\Models\Olt::where('status','online')->get(); @endphp
                                        @foreach($oltList as $olt)
                                            <option value="{{ $olt->id }}" {{ old('olt_id') == $olt->id ? 'selected' : '' }}>
                                                {{ $olt->nama }} ({{ $olt->ip_address }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('olt_id'))
                                        <div class="form-error">{{ $errors->first('olt_id') }}</div>
                                    @endif
                                </div>
                            </div>

                        </div>{{-- /modal-body --}}
                        <div class="modal-footer">
                            <button type="button" class="btn btn-ghost" @click="open = false">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Pelanggan
                            </button>
                        </div>
                    </form>
                </div>
            </template>
        </div>
        @endif
    </div>
</div>

{{-- ═══════════════════════════════════════════════
     STATS MINI
═══════════════════════════════════════════════ --}}
@php
    $total    = $pelanggans->total();
    $aktif    = \App\Models\Pelanggan::where('status','active')->count();
    $suspend  = \App\Models\Pelanggan::where('status','suspend')->count();
    $inactive = \App\Models\Pelanggan::where('status','inactive')->count();
@endphp

<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 20px;">
    <div class="stat-card indigo">
        <div class="stat-icon indigo"><i class="fas fa-users"></i></div>
        <div class="stat-value">{{ number_format($total) }}</div>
        <div class="stat-label">Total Pelanggan</div>
        <div class="stat-sub mute"><i class="fas fa-database"></i> semua status</div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon green"><i class="fas fa-circle-check"></i></div>
        <div class="stat-value">{{ number_format($aktif) }}</div>
        <div class="stat-label">Pelanggan Aktif</div>
        <div class="stat-sub up"><i class="fas fa-arrow-trend-up"></i> layanan berjalan</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-icon amber"><i class="fas fa-lock"></i></div>
        <div class="stat-value">{{ number_format($suspend) }}</div>
        <div class="stat-label">Diisolir</div>
        <div class="stat-sub down"><i class="fas fa-triangle-exclamation"></i> butuh perhatian</div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon red"><i class="fas fa-user-slash"></i></div>
        <div class="stat-value">{{ number_format($inactive) }}</div>
        <div class="stat-label">Tidak Aktif</div>
        <div class="stat-sub mute"><i class="fas fa-minus"></i> nonaktif</div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════
     TABLE CARD
═══════════════════════════════════════════════ --}}
<div class="card">

    {{-- ── Toolbar / Filters ── --}}
    <form method="GET" action="{{ route('pelanggan.index') }}" id="filterForm">
        <input type="hidden" name="sort_by" id="sort_by" value="{{ request('sort_by', 'created_at') }}">
        <input type="hidden" name="sort_dir" id="sort_dir" value="{{ request('sort_dir', 'desc') }}">
        
        <div class="table-toolbar">
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                {{-- Search --}}
                <div class="toolbar-search">
                    <i class="fas fa-magnifying-glass"></i>
                    <input type="text"
                           name="search"
                           id="searchInput"
                           value="{{ request('search') }}"
                           placeholder="Cari nama, PPPoE, IP, telp..."
                           autocomplete="off">
                </div>

                {{-- Status filter --}}
                <select name="status"
                        id="statusFilter"
                        class="form-control"
                        style="width:auto;height:32px;padding:0 10px;font-size:12px;">
                    <option value="">Semua Status</option>
                    <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Aktif</option>
                    <option value="suspend"  {{ request('status') === 'suspend'  ? 'selected' : '' }}>Isolir / Suspend</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>

                {{-- Paket filter --}}
                <select name="paket_id"
                        id="paketFilter"
                        class="form-control"
                        style="width:auto;height:32px;padding:0 10px;font-size:12px;">
                    <option value="">Semua Paket</option>
                    @foreach($pakets as $paket)
                        <option value="{{ $paket->id }}" {{ request('paket_id') == $paket->id ? 'selected' : '' }}>
                            {{ $paket->nama }}
                        </option>
                    @endforeach
                </select>
                
                {{-- NAS filter --}}
                <select name="nas_id"
                        id="nasFilter"
                        class="form-control"
                        style="width:auto;height:32px;padding:0 10px;font-size:12px;">
                    <option value="">Semua NAS</option>
                    @foreach($nases as $nas)
                        <option value="{{ $nas->id }}" {{ request('nas_id') == $nas->id ? 'selected' : '' }}>
                            {{ $nas->nama }}
                        </option>
                    @endforeach
                </select>

                @if(request()->hasAny(['search','status','paket_id','nas_id']))
                    <a href="{{ route('pelanggan.index') }}" class="btn btn-ghost btn-sm">
                        <i class="fas fa-xmark"></i> Reset
                    </a>
                @endif
            </div>

            <div class="toolbar-right">
                <button type="submit" class="btn btn-ghost btn-sm">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <span class="mono-mute" style="font-size:11px;">
                    {{ $pelanggans->total() }} data
                </span>
            </div>
        </div>
    </form>

    {{-- ── Table ── --}}
    <div class="card-body-flush" id="tableContainer">
        @include('pelanggan.partials.table')
    </div>

</div>{{-- /card --}}

{{-- ── Auto-open modal if validation error --}}
@if($errors->any())
@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    // Open the modal automatically if there are validation errors
});
document.addEventListener('DOMContentLoaded', function() {
    const hasErrors = {{ $errors->any() ? 'true' : 'false' }};
    if (hasErrors) {
        const btn = document.querySelector('[\\@click="open = true"]');
        if (btn) btn.click();
    }
});
</script>
@endpush
@endif

@push('scripts')
<script>
function updateBulkGenerate() {
    const checked = document.querySelectorAll('.row-checkbox:checked');
    const count = checked.length;
    
    // Bulk Invoice elements
    const btn = document.getElementById('btnBulkInvoice');
    const badge = document.getElementById('bulkCount');
    const modalBadge = document.getElementById('bulkCountModal');
    const hiddenInputsContainer = document.getElementById('hiddenBulkInputs');
    
    // Bulk Delete elements
    const btnDel = document.getElementById('btnBulkDelete');
    const badgeDel = document.getElementById('bulkDeleteCount');
    const modalBadgeDel = document.getElementById('bulkDeleteCountModal');
    const hiddenDelInputsContainer = document.getElementById('hiddenBulkDeleteInputs');
    
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    
    // Update button visibility and badge
    if (count > 0) {
        if (btn) btn.style.display = 'inline-block';
        if (btnDel) btnDel.style.display = 'inline-block';
        
        if (badge) badge.textContent = count;
        if (modalBadge) modalBadge.textContent = count;
        if (badgeDel) badgeDel.textContent = count;
        if (modalBadgeDel) modalBadgeDel.textContent = count;
    } else {
        if (btn) btn.style.display = 'none';
        if (btnDel) btnDel.style.display = 'none';
    }
    
    // Check/Uncheck "Select All" dynamically
    const allCheckboxes = document.querySelectorAll('.row-checkbox');
    if (allCheckboxes.length > 0 && count === allCheckboxes.length) {
        if (selectAllCheckbox) selectAllCheckbox.checked = true;
    } else {
        if (selectAllCheckbox) selectAllCheckbox.checked = false;
    }

    // Populate hidden form inputs
    if (hiddenInputsContainer) hiddenInputsContainer.innerHTML = '';
    if (hiddenDelInputsContainer) hiddenDelInputsContainer.innerHTML = '';
    
    checked.forEach(cb => {
        if (hiddenInputsContainer) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'pelanggan_ids[]';
            input.value = cb.value;
            hiddenInputsContainer.appendChild(input);
        }
        if (hiddenDelInputsContainer) {
            const inputDel = document.createElement('input');
            inputDel.type = 'hidden';
            inputDel.name = 'pelanggan_ids[]';
            inputDel.value = cb.value;
            hiddenDelInputsContainer.appendChild(inputDel);
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const rowCheckboxes = document.querySelectorAll('.row-checkbox');
            rowCheckboxes.forEach(cb => cb.checked = this.checked);
            updateBulkGenerate();
        });
    }

    // -- Live Search & Filtering --
    const filterForm = document.getElementById('filterForm');
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const paketFilter = document.getElementById('paketFilter');
    const nasFilter = document.getElementById('nasFilter');
    const tableContainer = document.getElementById('tableContainer');
    
    // Sort states
    const sortByInput = document.getElementById('sort_by');
    const sortDirInput = document.getElementById('sort_dir');

    // Debounce function
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    const fetchPelanggans = () => {
        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        
        // Append _t to avoid cache
        params.set('_t', new Date().getTime());
        
        fetch(`${window.location.pathname}?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.html) {
                tableContainer.innerHTML = data.html;
                updateBulkGenerate();
                attachSortListeners();
                
                // Update total count
                const totalText = document.querySelector('.toolbar-right .mono-mute');
                if (totalText) {
                    totalText.textContent = data.total + ' data';
                }
            }
        })
        .catch(error => console.error('Error fetching data:', error));
    };

    const debouncedFetch = debounce(fetchPelanggans, 300);

    // Attach listeners for live search
    if(searchInput) searchInput.addEventListener('input', debouncedFetch);
    if(statusFilter) statusFilter.addEventListener('change', fetchPelanggans);
    if(paketFilter) paketFilter.addEventListener('change', fetchPelanggans);
    if(nasFilter) nasFilter.addEventListener('change', fetchPelanggans);

    // Prevent default form submit if JS is enabled
    if(filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            fetchPelanggans();
        });
    }

    // Function to attach sort listeners to new HTML
    function attachSortListeners() {
        document.querySelectorAll('.sortable').forEach(el => {
            el.addEventListener('click', function(e) {
                e.preventDefault();
                const col = this.dataset.sort;
                let currentDir = sortDirInput.value;
                let currentSort = sortByInput.value;
                
                if (currentSort === col) {
                    sortDirInput.value = currentDir === 'asc' ? 'desc' : 'asc';
                } else {
                    sortByInput.value = col;
                    sortDirInput.value = 'asc';
                }
                
                fetchPelanggans();
            });
        });
    }
    attachSortListeners();

    // Auto-refresh data pelanggan setiap 10 detik
    setInterval(function() {
        // Jangan auto-refresh kalau sedang pilih checkbox (biar centangan ga ilang)
        if (document.querySelectorAll('.row-checkbox:checked').length > 0) return;

        // Cek apakah ada modal yang sedang terbuka (terlihat di layar)
        let modals = document.querySelectorAll('.modal-overlay');
        let isModalOpen = false;
        for(let i = 0; i < modals.length; i++) {
            if (modals[i].style.display !== 'none' && modals[i].style.display !== '') {
                isModalOpen = true;
                break;
            }
        }
        
        if (isModalOpen) return;

        // Use the same fetch function to refresh
        fetchPelanggans();
    }, 10000);
});
</script>
@endpush

@endsection
