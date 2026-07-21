@extends('layouts.app')
@section('title', 'Konfirmasi Pembayaran')
@section('page-title', 'Keuangan')
@section('breadcrumb', 'Konfirmasi Pembayaran')

@section('content')
@php
    $bankName    = \App\Models\SystemSetting::get('bank_name', 'BCA');
    $bankAccount = \App\Models\SystemSetting::get('bank_account', '1234567890');
    $bankHolder  = \App\Models\SystemSetting::get('bank_holder', 'PT. BINA RAJA SOLUSI');
@endphp

{{-- ── Page Header ── --}}
<div class="page-header">
    <div class="page-header-title">
        <h1><i class="fas fa-money-bill-transfer" style="color:var(--green);margin-right:8px;"></i>Konfirmasi Pembayaran</h1>
        <p>Catat dan kelola pembayaran tagihan pelanggan</p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('pembayaran.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Catat Pembayaran
        </a>
    </div>
</div>

{{-- ── Bank Info Card ── --}}
<div class="card" style="margin-bottom:20px;border-color:rgba(16,185,129,0.25);">
    <div class="card-header" style="background:rgba(16,185,129,0.05);">
        <div>
            <div class="card-title" style="color:var(--green);">
                <i class="fas fa-building-columns" style="margin-right:6px;"></i>Informasi Rekening Transfer
            </div>
            <div class="card-subtitle">Rekening resmi untuk pembayaran tagihan internet</div>
        </div>
        <span class="badge badge-active" style="font-size:12px;">Aktif</span>
    </div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:16px;">
            <div style="background:var(--bg-elevated);border-radius:var(--radius);padding:14px;border:1px solid var(--border);display:flex;flex-direction:column;gap:8px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <div style="font-size:10px;color:var(--text-2);text-transform:uppercase;letter-spacing:0.5px;">Bank BCA</div>
                    <i class="fas fa-building-columns" style="color:var(--sky);font-size:14px;"></i>
                </div>
                <div class="mono" style="font-size:20px;font-weight:700;color:var(--text-1);">6280939267</div>
                <div style="font-size:12px;color:var(--text-1);"><span style="color:var(--text-3);">a.n</span> AISYAH NURUL ISTIQOMAH</div>
            </div>
            <div style="background:var(--bg-elevated);border-radius:var(--radius);padding:14px;border:1px solid var(--border);display:flex;flex-direction:column;gap:8px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <div style="font-size:10px;color:var(--text-2);text-transform:uppercase;letter-spacing:0.5px;">Bank Mandiri</div>
                    <i class="fas fa-building-columns" style="color:var(--amber);font-size:14px;"></i>
                </div>
                <div class="mono" style="font-size:20px;font-weight:700;color:var(--text-1);">1760003390752</div>
                <div style="font-size:12px;color:var(--text-1);"><span style="color:var(--text-3);">a.n</span> BINA RAJA SOLUSI</div>
            </div>
            <div style="background:rgba(245,158,11,0.06);border-radius:var(--radius);padding:14px;border:1px solid rgba(245,158,11,0.2);">
                <div style="font-size:10px;color:var(--amber);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px;">
                    <i class="fas fa-triangle-exclamation"></i> Petunjuk
                </div>
                <div style="font-size:12px;color:var(--text-2);line-height:1.5;">
                    Pastikan nominal transfer sesuai dengan tagihan. Sertakan nomor invoice sebagai keterangan transfer.
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Pembayaran Table ── --}}
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Riwayat Pembayaran</div>
            <div class="card-subtitle">{{ $pembayarans->total() ?? $pembayarans->count() }} catatan pembayaran</div>
        </div>
        <a href="{{ route('pembayaran.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Catat Baru
        </a>
    </div>

    {{-- Toolbar Search --}}
    <form method="GET" action="{{ route('pembayaran.index') }}">
        <div class="table-toolbar">
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                <div class="toolbar-search">
                    <i class="fas fa-magnifying-glass"></i>
                    <input type="text"
                           name="search"
                           placeholder="No Invoice / Pelanggan..."
                           value="{{ request('search') }}">
                </div>
                <select name="metode" class="form-control form-control-mono" style="width:160px;height:32px;padding:0 8px;font-size:12px;">
                    <option value="">Semua Metode</option>
                    <option value="cash"             {{ request('metode') === 'cash'             ? 'selected' : '' }}>Cash</option>
                    <option value="transfer_bca"     {{ request('metode') === 'transfer_bca'     ? 'selected' : '' }}>Transfer BCA</option>
                    <option value="transfer_bri"     {{ request('metode') === 'transfer_bri'     ? 'selected' : '' }}>Transfer BRI</option>
                    <option value="transfer_mandiri" {{ request('metode') === 'transfer_mandiri' ? 'selected' : '' }}>Transfer Mandiri</option>
                    <option value="transfer_bni"     {{ request('metode') === 'transfer_bni'     ? 'selected' : '' }}>Transfer BNI</option>
                    <option value="transfer_lain"    {{ request('metode') === 'transfer_lain'    ? 'selected' : '' }}>Transfer Lain</option>
                </select>
                <button type="submit" class="btn btn-ghost btn-sm">
                    <i class="fas fa-filter"></i> Filter
                </button>
                @if(request()->hasAny(['search','metode']))
                    <a href="{{ route('pembayaran.index') }}" class="btn btn-ghost btn-sm">
                        <i class="fas fa-xmark"></i> Reset
                    </a>
                @endif
            </div>
        </div>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Invoice</th>
                    <th>Pelanggan</th>
                    <th>Nominal</th>
                    <th>Metode</th>
                    <th>Tgl Bayar</th>
                    <th>Dicatat Oleh</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pembayarans as $idx => $pay)
                <tr>
                    <td class="mono-mute">{{ $pembayarans->firstItem() + $idx }}</td>
                    <td>
                        <a href="{{ route('invoice.show', $pay->invoice_id) }}"
                           class="mono"
                           style="text-decoration:none;color:var(--sky);">
                            {{ $pay->invoice->no_invoice ?? '—' }}
                        </a>
                    </td>
                    <td>
                        <div style="font-weight:500;color:var(--text-1);">
                            {{ $pay->invoice->pelanggan->nama ?? '—' }}
                        </div>
                        <div class="mono-mute" style="font-size:11px;">
                            {{ $pay->invoice->periode ?? '' }}
                        </div>
                    </td>
                    <td>
                        <span class="mono" style="color:var(--green);">
                            Rp {{ number_format($pay->nominal, 0, ',', '.') }}
                        </span>
                    </td>
                    <td>
                        <span style="display:inline-flex;align-items:center;gap:5px;">
                            @if($pay->metode === 'cash')
                                <i class="fas fa-money-bill" style="color:var(--green);font-size:11px;"></i>
                            @else
                                <i class="fas fa-building-columns" style="color:var(--sky);font-size:11px;"></i>
                            @endif
                            <span class="mono-mute">{{ $pay->metode_label }}</span>
                        </span>
                    </td>
                    <td class="mono-mute">
                        {{ $pay->tgl_bayar ? $pay->tgl_bayar->format('d M Y') : '—' }}
                    </td>
                    <td>
                        <div style="font-size:13px;color:var(--text-2);">{{ $pay->user->name ?? '—' }}</div>
                        <div class="mono-mute" style="font-size:10px;">{{ $pay->created_at->diffForHumans() }}</div>
                    </td>
                    <td>
                        <div style="display:flex;gap:4px;">
                            <a href="{{ route('pembayaran.show', $pay->id) }}"
                               class="btn btn-ghost btn-xs"
                               title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($pay->bukti_transfer)
                            <a href="{{ Storage::url($pay->bukti_transfer) }}"
                               target="_blank"
                               class="btn btn-sky btn-xs"
                               title="Lihat Bukti Transfer">
                                <i class="fas fa-image"></i>
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <i class="fas fa-money-bill-transfer"></i>
                            <h3>Belum ada catatan pembayaran</h3>
                            <p>
                                @if(request()->hasAny(['search','metode']))
                                    Tidak ada pembayaran yang cocok dengan filter. <a href="{{ route('pembayaran.index') }}" style="color:var(--indigo);">Reset filter</a>
                                @else
                                    Catat pembayaran pertama dengan menekan tombol "Catat Pembayaran"
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
    @if($pembayarans->hasPages())
        {{ $pembayarans->appends(request()->query())->links() }}
    @endif
</div>
@endsection
