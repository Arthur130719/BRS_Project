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
        @php
            $rekeningBanks = json_decode(\App\Models\SystemSetting::get('rekening_banks', '[]'), true);
            
            $totalByMetode = [];
            foreach ($totalPerBank as $row) {
                $totalByMetode[$row->metode] = $row->total;
            }
        @endphp
        
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px;">
            @forelse($rekeningBanks as $rek)
                @php
                    $bankNameStr = $rek['bank'] ?? 'Bank';
                    $bankName = strtolower($bankNameStr);
                    $an = $rek['an'] ?? '';
                    
                    $metodeLabel = "Transfer $bankNameStr" . ($an ? " (a.n $an)" : "");
                    $totalMasuk = $totalByMetode[$metodeLabel] ?? 0;
                    
                    if (str_contains($bankName, 'bca')) {
                        $color = '#0066AE'; // BCA Blue
                        $icon = 'fa-building-columns';
                    } elseif (str_contains($bankName, 'mandiri')) {
                        $color = '#F2A900'; // Mandiri Yellow
                        $icon = 'fa-building-columns';
                    } elseif (str_contains($bankName, 'bri')) {
                        $color = '#00529C'; // BRI Blue
                        $icon = 'fa-building-columns';
                    } elseif (str_contains($bankName, 'bni')) {
                        $color = '#F15A23'; // BNI Orange
                        $icon = 'fa-building-columns';
                    } elseif (str_contains($bankName, 'dana')) {
                        $color = '#118EEA'; // Dana Blue
                        $icon = 'fa-wallet';
                    } elseif (str_contains($bankName, 'gopay') || str_contains($bankName, 'ovo')) {
                        $color = '#00A550'; // GoPay/OVO Green
                        $icon = 'fa-wallet';
                    } else {
                        $color = 'var(--sky)';
                        $icon = 'fa-building-columns';
                    }
                @endphp
                <div style="background:var(--bg-elevated);border-radius:var(--radius);padding:16px;border:1px solid var(--border);border-left:4px solid {{ $color }};display:flex;flex-direction:column;gap:8px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <div style="font-size:11px;font-weight:700;color:{{ $color }};text-transform:uppercase;letter-spacing:0.5px;">{{ $rek['bank'] ?? '-' }}</div>
                        <i class="fas {{ $icon }}" style="color:{{ $color }};font-size:16px;"></i>
                    </div>
                    <div class="mono" style="font-size:22px;font-weight:700;color:var(--text-1);letter-spacing:1px;">{{ $rek['norek'] ?? '-' }}</div>
                    <div style="font-size:12px;color:var(--text-1);"><span style="color:var(--text-3);">a.n</span> {{ $rek['an'] ?? '-' }}</div>
                    
                    <div style="margin-top:4px;padding-top:10px;border-top:1px dashed var(--border);display:flex;justify-content:space-between;align-items:center;">
                        <div style="font-size:11px;color:var(--text-3);font-weight:500;">Total Diterima</div>
                        <div class="mono" style="font-size:13px;font-weight:700;color:var(--text-1);">Rp {{ number_format($totalMasuk, 0, ',', '.') }}</div>
                    </div>
                </div>
            @empty
                <div style="padding:15px;color:var(--text-3);font-size:13px;border:1px dashed var(--border);border-radius:8px;text-align:center;">
                    Belum ada informasi rekening yang ditambahkan. Silakan atur di menu Pengaturan.
                </div>
            @endforelse
            
            <div style="background:rgba(245,158,11,0.06);border-radius:var(--radius);padding:14px;border:1px solid rgba(245,158,11,0.2);">
                <div style="font-size:10px;color:var(--amber);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px;font-weight:600;">
                    <i class="fas fa-triangle-exclamation"></i> Petunjuk Transfer
                </div>
                <div style="font-size:12px;color:var(--text-2);line-height:1.5;">
                    Pastikan nominal transfer sesuai dengan tagihan. Sertakan <strong>Nomor Invoice</strong> sebagai berita/keterangan transfer untuk memudahkan verifikasi.
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Summary Total Pendapatan ── --}}
<div style="margin-bottom:20px;">

    @php
        // Helper: format Rupiah
        $fmt = fn($n) => 'Rp ' . number_format($n, 0, ',', '.');

        // Kelompokkan totalPerBank menjadi label yang rapi
        $bankGroups = [];
        foreach ($totalPerBank as $row) {
            $metode = $row->metode;
            $label  = match(true) {
                str_starts_with($metode, 'transfer_bca')     => 'BCA',
                str_starts_with($metode, 'transfer_bri')     => 'BRI',
                str_starts_with($metode, 'transfer_mandiri') => 'Mandiri',
                str_starts_with($metode, 'transfer_bni')     => 'BNI',
                $metode === 'Transfer Lain'                  => 'Lainnya',
                default => ucwords(str_replace(['transfer_', '_'], ['', ' '], $metode)),
            };
            // Jika metode dari rekening dinamis (misal "Transfer BCA (a.n ...)")
            if (str_starts_with($metode, 'Transfer ')) {
                $label = explode(' (', ltrim($metode, 'Transfer '))[0];
                $label = ltrim($label, 'Transfer ');
                // Ambil nama bank saja
                $parts = explode(' ', $metode, 3);
                $label = $parts[1] ?? $metode;
            }
            if (!isset($bankGroups[$label])) {
                $bankGroups[$label] = 0;
            }
            $bankGroups[$label] += $row->total;
        }
    @endphp

    {{-- Row: Cash + Transfer Cards --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;margin-bottom:14px;">

        {{-- Total Cash --}}
        <div style="background:var(--bg-surface);border:1px solid rgba(16,185,129,0.3);border-left:4px solid var(--green);border-radius:var(--radius-xl);padding:18px 20px;display:flex;flex-direction:column;gap:6px;">
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:var(--green);">
                    <i class="fas fa-money-bill-wave" style="margin-right:5px;"></i>Total Cash
                </div>
                <div style="width:32px;height:32px;border-radius:50%;background:rgba(16,185,129,0.12);display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-money-bill" style="color:var(--green);font-size:13px;"></i>
                </div>
            </div>
            <div class="mono" style="font-size:22px;font-weight:700;color:var(--text-1);">{{ $fmt($totalCashBulanIni) }}</div>
            <div style="font-size:11px;color:var(--text-4);">Pembayaran tunai langsung (Bulan Ini)</div>
        </div>

        {{-- Total Transfer per Bank --}}
        @foreach($bankGroups as $bankLabel => $bankTotal)
        @php
            $bankLow = strtolower($bankLabel);
            $bankColor = match(true) {
                str_contains($bankLow, 'bca')     => '#0066AE',
                str_contains($bankLow, 'mandiri') => '#F2A900',
                str_contains($bankLow, 'bri')     => '#00529C',
                str_contains($bankLow, 'bni')     => '#F15A23',
                str_contains($bankLow, 'dana')    => '#118EEA',
                str_contains($bankLow, 'gopay')   => '#00A550',
                default                           => 'var(--sky)',
            };
        @endphp
        <div style="background:var(--bg-surface);border:1px solid rgba(14,165,233,0.25);border-left:4px solid {{ $bankColor }};border-radius:var(--radius-xl);padding:18px 20px;display:flex;flex-direction:column;gap:6px;">
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:{{ $bankColor }};">
                    <i class="fas fa-building-columns" style="margin-right:5px;"></i>Transfer {{ $bankLabel }}
                </div>
                <div style="width:32px;height:32px;border-radius:50%;background:rgba(14,165,233,0.1);display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-building-columns" style="color:{{ $bankColor }};font-size:13px;"></i>
                </div>
            </div>
            <div class="mono" style="font-size:22px;font-weight:700;color:var(--text-1);">{{ $fmt($bankTotal) }}</div>
            <div style="font-size:11px;color:var(--text-4);">Pembayaran via rekening {{ $bankLabel }}</div>
        </div>
        @endforeach

    </div>

    {{-- Row: Total Transfer + Grand Total --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">

        {{-- Total Semua Transfer --}}
        <div style="background:var(--bg-surface);border:1px solid rgba(14,165,233,0.3);border-left:4px solid var(--sky);border-radius:var(--radius-xl);padding:18px 20px;display:flex;flex-direction:column;gap:6px;">
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:var(--sky);">
                    <i class="fas fa-arrows-left-right" style="margin-right:5px;"></i>Total Semua Transfer
                </div>
                <div style="width:32px;height:32px;border-radius:50%;background:rgba(14,165,233,0.12);display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-building-columns" style="color:var(--sky);font-size:13px;"></i>
                </div>
            </div>
            <div class="mono" style="font-size:22px;font-weight:700;color:var(--text-1);">{{ $fmt($totalTransferAllBulanIni) }}</div>
            <div style="font-size:11px;color:var(--text-4);">Gabungan semua rekening bank (Bulan Ini)</div>
        </div>

        {{-- Grand Total --}}
        <div style="background:linear-gradient(135deg, rgba(99,102,241,0.15) 0%, rgba(139,92,246,0.15) 100%);border:1px solid rgba(99,102,241,0.4);border-left:4px solid var(--indigo);border-radius:var(--radius-xl);padding:18px 20px;display:flex;flex-direction:column;gap:6px;position:relative;overflow:hidden;">
            <div style="position:absolute;right:-10px;bottom:-10px;font-size:60px;color:rgba(99,102,241,0.06);pointer-events:none;">
                <i class="fas fa-sack-dollar"></i>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:var(--indigo);">
                    <i class="fas fa-trophy" style="margin-right:5px;"></i>Grand Total Pendapatan
                </div>
                <div style="width:32px;height:32px;border-radius:50%;background:rgba(99,102,241,0.2);display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-sack-dollar" style="color:var(--indigo);font-size:13px;"></i>
                </div>
            </div>
            <div class="mono" style="font-size:26px;font-weight:700;color:var(--text-1);letter-spacing:-0.5px;">{{ $fmt($grandTotalBulanIni) }}</div>
            <div style="font-size:11px;color:var(--text-1);display:flex;justify-content:space-between;margin-top:2px;">
                <span>Total Keseluruhan (All Time):</span>
                <span class="mono" style="font-weight:700;">{{ $fmt($grandTotalKeseluruhan) }}</span>
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
                
                {{-- Filter Bulan & Tahun --}}
                <select name="bulan" class="form-control form-control-mono" style="width:110px;height:32px;padding:0 8px;font-size:12px;">
                    @php
                        $months = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni',
                                   '07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
                    @endphp
                    @foreach($months as $num => $name)
                        <option value="{{ $num }}" {{ $bulan == $num ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                <select name="tahun" class="form-control form-control-mono" style="width:80px;height:32px;padding:0 8px;font-size:12px;">
                    @php $currY = date('Y'); @endphp
                    @foreach(range($currY - 2, $currY + 1) as $y)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>

                @php
                    $rekeningBanks = json_decode(\App\Models\SystemSetting::get('rekening_banks', '[]'), true);
                @endphp
                <select name="metode" class="form-control form-control-mono" style="width:160px;height:32px;padding:0 8px;font-size:12px;">
                    <option value="">Semua Metode</option>
                    <option value="cash" {{ request('metode') === 'cash' ? 'selected' : '' }}>Cash</option>
                    @foreach($rekeningBanks as $rek)
                        @php 
                            $bank = $rek['bank'] ?? 'Bank';
                            $an = $rek['an'] ?? '';
                            $val = "Transfer $bank" . ($an ? " (a.n $an)" : "");
                        @endphp
                        <option value="{{ $val }}" {{ request('metode') === $val ? 'selected' : '' }}>{{ $val }}</option>
                    @endforeach
                    <option value="Transfer Lain" {{ request('metode') === 'Transfer Lain' ? 'selected' : '' }}>Transfer Lain</option>
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
