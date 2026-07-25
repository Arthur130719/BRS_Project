@extends('layouts.app')
@section('title', 'Billing & Invoice')
@section('page-title', 'Keuangan')
@section('breadcrumb', 'Billing & Invoice')

@section('content')
@php
    $totalInvoice  = $invoices->total() ?? $invoices->count();
    $totalLunas    = $invoices->getCollection()->where('status', 'paid')->count();
    $totalUnpaid   = $invoices->getCollection()->where('status', 'unpaid')->count();
    $totalPartial  = $invoices->getCollection()->where('status', 'partial')->count();
    $totalNominal  = $invoices->getCollection()->sum('nominal');

    // Full counts for stats (passed from controller if available, else fallback)
    $countAll     = $statAll     ?? \App\Models\Invoice::count();
    $countLunas   = $statLunas   ?? \App\Models\Invoice::where('status','paid')->count();
    $countUnpaid  = $statUnpaid  ?? \App\Models\Invoice::where('status','unpaid')->count();
    $countPartial = $statPartial ?? \App\Models\Invoice::where('status','partial')->count();
@endphp

{{-- ── Page Header ── --}}
<div class="page-header">
    <div class="page-header-title">
        <h1><i class="fas fa-file-invoice-dollar" style="color:var(--indigo);margin-right:8px;"></i>Billing & Invoice</h1>
        <p>Kelola tagihan dan status pembayaran pelanggan</p>
    </div>
    <div class="page-header-actions" x-data="{ open: false }">
        <button @click="open = true" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Invoice
        </button>

        {{-- ── Modal Tambah Invoice ── --}}
        <template x-teleport="body">
        <div x-show="open" x-cloak class="modal-overlay" @click.self="open = false" style="display:none;">
            <div class="modal modal-lg" @click.stop>
                <div class="modal-header">
                    <span class="modal-title"><i class="fas fa-file-invoice-dollar" style="color:var(--indigo);margin-right:8px;"></i>Buat Invoice Baru</span>
                    <button @click="open = false" class="modal-close"><i class="fas fa-xmark"></i></button>
                </div>
                <form method="POST" action="{{ route('invoice.store') }}">
                    @csrf
                    <div class="modal-body">
                        @if($errors->any())
                            <div class="alert alert-danger" style="margin-bottom:14px;">
                                <i class="fas fa-circle-exclamation"></i>
                                <div>
                                    @foreach($errors->all() as $err)
                                        <div>{{ $err }}</div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="form-group">
                            <label class="form-label">Pelanggan <span style="color:var(--red);">*</span></label>
                            <select name="pelanggan_id" class="form-control" required onchange="document.getElementById('nominal_input').value = this.options[this.selectedIndex].getAttribute('data-harga') || '';">
                                <option value="" data-harga="">— Pilih Pelanggan —</option>
                                @foreach($pelanggans ?? [] as $p)
                                    <option value="{{ $p->id }}" data-harga="{{ $p->paket->harga ?? 0 }}" {{ old('pelanggan_id') == $p->id ? 'selected' : '' }}>
                                        {{ $p->nama }} — {{ $p->username_pppoe }} (Rp {{ number_format($p->paket->harga ?? 0, 0, ',', '.') }})
                                    </option>
                                @endforeach
                            </select>
                            @if($errors->has('pelanggan_id'))
                                <div class="form-error">{{ $errors->first('pelanggan_id') }}</div>
                            @endif
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Periode <span style="color:var(--red);">*</span></label>
                                <input type="text"
                                       name="periode"
                                       class="form-control form-control-mono"
                                       placeholder="mis. Juni 2025"
                                       value="{{ old('periode', now()->format('F Y')) }}"
                                       required>
                                <div class="form-hint">Contoh: Juni 2025</div>
                                @if($errors->has('periode'))
                                    <div class="form-error">{{ $errors->first('periode') }}</div>
                                @endif
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nominal (Rp) <span style="color:var(--red);">*</span></label>
                                <input type="number"
                                       id="nominal_input"
                                       name="nominal"
                                       class="form-control form-control-mono"
                                       placeholder="mis. 150000"
                                       value="{{ old('nominal') }}"
                                       min="0"
                                       required>
                                @if($errors->has('nominal'))
                                    <div class="form-error">{{ $errors->first('nominal') }}</div>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Tanggal Jatuh Tempo <span style="color:var(--red);">*</span></label>
                            <input type="date"
                                   name="tgl_jatuh_tempo"
                                   class="form-control form-control-mono"
                                   value="{{ old('tgl_jatuh_tempo', now()->addDays(14)->format('Y-m-d')) }}"
                                   required>
                            @if($errors->has('tgl_jatuh_tempo'))
                                <div class="form-error">{{ $errors->first('tgl_jatuh_tempo') }}</div>
                            @endif
                        </div>

                        <div class="form-group">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan"
                                      class="form-control"
                                      rows="2"
                                      placeholder="Keterangan tambahan (opsional)">{{ old('keterangan') }}</textarea>
                            @if($errors->has('keterangan'))
                                <div class="form-error">{{ $errors->first('keterangan') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" @click="open = false" class="btn btn-ghost">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Invoice
                        </button>
                    </div>
                </form>
            </div>
        </div>
        </template>
    </div>
</div>

{{-- ── Stats Row ── --}}
<div class="stats-grid" style="grid-template-columns: repeat(4,1fr);">
    <div class="stat-card indigo">
        <div class="stat-icon indigo"><i class="fas fa-file-invoice-dollar"></i></div>
        <div class="stat-value">{{ number_format($countAll) }}</div>
        <div class="stat-label">Total Invoice</div>
        <div class="stat-sub mute"><i class="fas fa-database"></i> Semua periode</div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon green"><i class="fas fa-circle-check"></i></div>
        <div class="stat-value">{{ number_format($countLunas) }}</div>
        <div class="stat-label">Lunas</div>
        @if($countAll > 0)
        <div class="stat-sub up"><i class="fas fa-arrow-up"></i> {{ round($countLunas / max($countAll,1) * 100) }}% dari total</div>
        @endif
    </div>
    <div class="stat-card red">
        <div class="stat-icon red"><i class="fas fa-clock"></i></div>
        <div class="stat-value">{{ number_format($countUnpaid) }}</div>
        <div class="stat-label">Belum Lunas</div>
        @if($countUnpaid > 0)
        <div class="stat-sub down"><i class="fas fa-triangle-exclamation"></i> Perlu tindakan</div>
        @endif
    </div>
    <div class="stat-card amber">
        <div class="stat-icon amber"><i class="fas fa-hourglass-half"></i></div>
        <div class="stat-value">{{ number_format($countPartial) }}</div>
        <div class="stat-label">Pembayaran Sebagian</div>
        @if($countPartial > 0)
        <div class="stat-sub mute"><i class="fas fa-info-circle"></i> Perlu pelunasan</div>
        @endif
    </div>
</div>

{{-- ── Main Card ── --}}
<div class="card" x-data="{ modalLunas: null }">
    {{-- Toolbar --}}
    <form method="GET" action="{{ route('invoice.index') }}">
        <div class="table-toolbar">
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                {{-- Search --}}
                <div class="toolbar-search">
                    <i class="fas fa-magnifying-glass"></i>
                    <input type="text"
                           name="search"
                           placeholder="No Invoice / Pelanggan..."
                           value="{{ request('search') }}">
                </div>
                {{-- Status Filter --}}
                <select name="status" class="form-control form-control-mono" style="width:140px;height:32px;padding:0 8px;font-size:12px;">
                    <option value="">Semua Status</option>
                    <option value="unpaid"  {{ request('status') === 'unpaid'  ? 'selected' : '' }}>Belum Lunas</option>
                    <option value="paid"    {{ request('status') === 'paid'    ? 'selected' : '' }}>Lunas</option>
                    <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Sebagian</option>
                </select>
                {{-- Periode Filter --}}
                <input type="text"
                       name="periode"
                       class="form-control form-control-mono"
                       style="width:130px;height:32px;padding:0 10px;font-size:12px;"
                       placeholder="Periode..."
                       value="{{ request('periode') }}">
                <button type="submit" class="btn btn-ghost btn-sm">
                    <i class="fas fa-filter"></i> Filter
                </button>
                @if(request()->hasAny(['search','status','periode']))
                    <a href="{{ route('invoice.index') }}" class="btn btn-ghost btn-sm">
                        <i class="fas fa-xmark"></i> Reset
                    </a>
                @endif
            </div>
            <div class="toolbar-right">
                <span class="mono-mute">{{ $invoices->total() ?? $invoices->count() }} invoice</span>
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>No Invoice</th>
                    <th>Pelanggan</th>
                    <th>Periode</th>
                    <th>Nominal</th>
                    <th>Jatuh Tempo</th>
                    <th>Status</th>
                    <th>Metode</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $idx => $inv)
                @php
                    $overdue = ($inv->status === 'unpaid' || $inv->status === 'partial') && $inv->tgl_jatuh_tempo && $inv->tgl_jatuh_tempo->isPast();
                @endphp
                <tr style="{{ $overdue ? 'border-left: 3px solid var(--red); background: rgba(239,68,68,0.03);' : '' }}">
                    <td class="mono-mute">{{ $invoices->firstItem() + $idx }}</td>
                    <td>
                        <a href="{{ route('invoice.show', $inv->id) }}"
                           class="mono"
                           style="text-decoration:none;color:var(--sky);">
                            {{ $inv->no_invoice }}
                        </a>
                    </td>
                    <td>
                        <div style="font-weight:500;color:var(--text-1);">{{ $inv->pelanggan->nama ?? '—' }}</div>
                        <div class="mono-mute" style="font-size:11px;margin-top:1px;">{{ $inv->pelanggan->username_pppoe ?? '' }}</div>
                    </td>
                    <td class="mono-mute">{{ $inv->periode }}</td>
                    <td>
                        <span class="mono" style="color:var(--text-1);">
                            Rp {{ number_format($inv->nominal, 0, ',', '.') }}
                        </span>
                    </td>
                    <td>
                        @if($inv->tgl_jatuh_tempo)
                            <span class="{{ $overdue ? '' : 'mono-mute' }}"
                                  style="{{ $overdue ? 'color:var(--red);font-family:JetBrains Mono,monospace;font-size:12px;font-weight:600;' : '' }}">
                                {{ $overdue ? '⚠ ' : '' }}{{ $inv->tgl_jatuh_tempo->format('d M Y') }}
                            </span>
                        @else
                            <span class="mono-mute">—</span>
                        @endif
                    </td>
                    <td>
                        @if($inv->status === 'paid')
                            <span class="badge badge-paid">Lunas</span>
                        @elseif($inv->status === 'partial')
                            <span class="badge badge-partial">Sebagian</span>
                        @else
                            <span class="badge badge-unpaid">Belum Lunas</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $lastPay = $inv->pembayarans->last();
                        @endphp
                        @if($lastPay)
                            <span class="mono-mute">{{ $lastPay->metode_label }}</span>
                        @else
                            <span class="mono-mute">—</span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex;gap:4px;flex-wrap:wrap;">
                            {{-- Detail --}}
                            <a href="{{ route('invoice.show', $inv->id) }}"
                               class="btn btn-ghost btn-xs"
                               title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            {{-- PDF --}}
                            <a href="{{ route('invoice.pdf', $inv->id) }}"
                               class="btn btn-sky btn-xs"
                               title="Export PDF"
                               target="_blank">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                            {{-- Tandai Lunas --}}
                            @if($inv->status !== 'paid')
                                <button type="button" @click="modalLunas = {{ $inv->id }}" class="btn btn-success btn-xs" title="Tandai Lunas">
                                    <i class="fas fa-check"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <h3>Belum ada invoice</h3>
                            <p>
                                @if(request()->hasAny(['search','status','periode']))
                                    Tidak ada invoice yang cocok dengan filter. <a href="{{ route('invoice.index') }}" style="color:var(--indigo);">Reset filter</a>
                                @else
                                    Buat invoice baru dengan menekan tombol "Tambah Invoice"
                                @endif
                            </p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    {{-- Pagination --}}
    @if($invoices->hasPages())
        {{ $invoices->appends(request()->query())->links() }}
    @endif

    {{-- Modal Lunas Cepat --}}
    <template x-teleport="body">
        <div x-show="modalLunas !== null" x-cloak class="modal-overlay" @click.self="modalLunas = null" style="display:flex;">
            <div class="modal" @click.stop>
                <div class="modal-header">
                    <span class="modal-title"><i class="fas fa-money-bill-transfer" style="color:var(--green);margin-right:8px;"></i>Pilih Metode Pembayaran</span>
                    <button @click="modalLunas = null" class="modal-close"><i class="fas fa-xmark"></i></button>
                </div>
                <form method="POST" :action="'{{ url('invoice') }}/' + modalLunas + '/lunas'">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">Metode Pembayaran <span style="color:var(--red);">*</span></label>
                            @php
                                $rekeningBanks = json_decode(\App\Models\SystemSetting::get('rekening_banks', '[]'), true);
                            @endphp
                            <select name="metode" class="form-control" required>
                                <option value="cash">Cash (Tunai)</option>
                                @foreach($rekeningBanks as $rek)
                                    @php
                                        $bank = $rek['bank'] ?? 'Bank';
                                        $an = $rek['an'] ?? '';
                                        $label = "Transfer $bank" . ($an ? " (a.n $an)" : "");
                                    @endphp
                                    <option value="{{ $label }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-ghost" @click="modalLunas = null">Batal</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Konfirmasi Lunas</button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

{{-- Auto-open modal if there are validation errors --}}
@if($errors->any())
<script>
    document.addEventListener('alpine:init', () => {
        // open modal when errors exist
    });
    document.addEventListener('DOMContentLoaded', function () {
        // trigger via Alpine
        window._invoiceModalError = true;
    });
</script>
@push('scripts')
<script>
    document.addEventListener('alpine:initialized', () => {
        if (window._invoiceModalError) {
            // find and open the modal
            const btn = document.querySelector('[\\@click="open = true"]');
            if (btn) btn.click();
        }
    });
</script>
@endpush
@endif
@endsection
