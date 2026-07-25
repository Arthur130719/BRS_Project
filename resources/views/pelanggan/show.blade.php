@extends('layouts.app')
@section('title', 'Detail Pelanggan — ' . $pelanggan->nama)
@section('page-title', 'Pelanggan')
@section('breadcrumb', 'Pelanggan / Detail')

@section('content')

{{-- ═══════════════════════════════════════════════
     PAGE HEADER
═══════════════════════════════════════════════ --}}
<div class="page-header">
    <div class="page-header-title">
        <h1 style="display:flex;align-items:center;gap:10px;">
            <span style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--indigo-dark),var(--indigo));display:inline-flex;align-items:center;justify-content:center;font-size:15px;font-weight:700;color:white;flex-shrink:0;">
                {{ strtoupper(substr($pelanggan->nama, 0, 1)) }}
            </span>
            {{ $pelanggan->nama }}
        </h1>
        <p style="margin-top:4px;display:flex;align-items:center;gap:10px;">
            <span class="mono-mute">{{ $pelanggan->username_pppoe }}</span>
            <span style="color:var(--border);">·</span>
            {!! $pelanggan->status_badge !!}
            @if($pelanggan->paket)
                <span style="color:var(--border);">·</span>
                <span style="font-size:12px;color:var(--text-3);">{{ $pelanggan->paket->nama }}</span>
            @endif
        </p>
    </div>
    <div class="page-header-actions" x-data="{ isolirModal: false, aktifkanModal: false }">
        {{-- Kembali --}}
        <a href="{{ route('pelanggan.index') }}" class="btn btn-ghost">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>

        {{-- Edit --}}
        @if(auth()->user()->isAdmin())
        <a href="{{ route('pelanggan.edit', $pelanggan->id) }}" class="btn btn-ghost">
            <i class="fas fa-pen"></i> Edit
        </a>
        @endif

        {{-- Isolir / Aktifkan --}}
        @if($pelanggan->status === 'active')
            <button @click="isolirModal = true" class="btn btn-warning">
                <i class="fas fa-lock"></i> Isolir
            </button>
        @elseif($pelanggan->status === 'suspend')
            <button @click="aktifkanModal = true" class="btn btn-success">
                <i class="fas fa-lock-open"></i> Aktifkan
            </button>
        @endif

        {{-- ── MODAL ISOLIR ── --}}
        <template x-teleport="body">
            <div x-show="isolirModal"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="modal-overlay"
             @click.self="isolirModal = false"
             style="display:none;">
            <div class="modal" @click.stop>
                <div class="modal-header">
                    <span class="modal-title" style="color:var(--amber);">
                        <i class="fas fa-lock" style="margin-right:8px;"></i>Konfirmasi Isolir Pelanggan
                    </span>
                    <button class="modal-close" @click="isolirModal = false"><i class="fas fa-xmark"></i></button>
                </div>
                <form method="POST" action="{{ route('pelanggan.suspend', $pelanggan->id) }}">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-warning" style="margin-bottom:14px;">
                            <i class="fas fa-triangle-exclamation"></i>
                            Pelanggan <strong>{{ $pelanggan->nama }}</strong> akan diisolir dan kehilangan akses internet.
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Alasan Isolir</label>
                            <textarea name="alasan"
                                      class="form-control"
                                      rows="3"
                                      placeholder="Contoh: Tagihan belum dibayar bulan ini...">{{ old('alasan') }}</textarea>
                            <div class="form-hint">Alasan akan dicatat dalam log isolir.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-ghost" @click="isolirModal = false">Batal</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-lock"></i> Isolir Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
        </template>

        {{-- ── MODAL AKTIFKAN ── --}}
        <template x-teleport="body">
            <div x-show="aktifkanModal"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="modal-overlay"
             @click.self="aktifkanModal = false"
             style="display:none;">
            <div class="modal" @click.stop>
                <div class="modal-header">
                    <span class="modal-title" style="color:var(--green);">
                        <i class="fas fa-lock-open" style="margin-right:8px;"></i>Aktifkan Kembali Pelanggan
                    </span>
                    <button class="modal-close" @click="aktifkanModal = false"><i class="fas fa-xmark"></i></button>
                </div>
                <form method="POST" action="{{ route('pelanggan.aktifkan', $pelanggan->id) }}">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-success" style="margin-bottom:14px;">
                            <i class="fas fa-circle-check"></i>
                            Pelanggan <strong>{{ $pelanggan->nama }}</strong> akan diaktifkan kembali dan dapat menggunakan layanan internet.
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Keterangan</label>
                            <textarea name="alasan"
                                      class="form-control"
                                      rows="2"
                                      placeholder="Contoh: Tagihan sudah dilunasi...">{{ old('alasan') }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-ghost" @click="aktifkanModal = false">Batal</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-lock-open"></i> Aktifkan Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
        </template>

    </div>
</div>

{{-- ═══════════════════════════════════════════════
     2-COLUMN: LEFT = INFO  |  RIGHT = ONU
═══════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:1fr 380px;gap:16px;margin-bottom:16px;">

    {{-- ── LEFT: Detail Info ── --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title"><i class="fas fa-id-card" style="margin-right:6px;color:var(--indigo);"></i>Informasi Pelanggan</div>
                <div class="card-subtitle">Data lengkap dan konfigurasi layanan</div>
            </div>
        </div>
        <div class="card-body">
            {{-- Section: Identitas --}}
            <div style="font-size:10px;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:var(--text-4);margin-bottom:8px;">Identitas</div>

            <div class="info-row">
                <span class="key">Nama Lengkap</span>
                <span class="val" style="color:var(--text-1);font-family:inherit;font-size:13px;font-weight:500;">{{ $pelanggan->nama }}</span>
            </div>
            <div class="info-row">
                <span class="key">Username PPPoE</span>
                <span class="val mono">{{ $pelanggan->username_pppoe }}</span>
            </div>
            <div class="info-row" x-data="{ show: false }">
                <span class="key">Password PPPoE</span>
                <span class="val mono" style="display:flex; align-items:center; gap:8px;">
                    <span x-show="!show" style="letter-spacing:2px; font-size:10px; margin-top:2px;">••••••••</span>
                    <span x-show="show" x-cloak>{{ $pelanggan->password_pppoe ?? '—' }}</span>
                    <button @click="show = !show" class="btn btn-ghost" style="padding:0; min-height:0; height:auto; border:none; background:transparent; color:var(--text-4);">
                        <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </button>
                </span>
            </div>
            <div class="info-row">
                <span class="key">No. Telepon</span>
                <span class="val">
                    {{ $pelanggan->phone ?? '—' }}
                    @if($pelanggan->phone_2)
                        <br><span style="font-size:11px;color:var(--text-3);">Alt: {{ $pelanggan->phone_2 }}</span>
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="key">Alamat</span>
                <span class="val" style="text-align:right;max-width:55%;word-break:break-word;">{{ $pelanggan->alamat ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="key">Koordinat</span>
                <span class="val">
                    @if($pelanggan->latitude && $pelanggan->longitude)
                        <span class="mono">{{ $pelanggan->latitude }}, {{ $pelanggan->longitude }}</span>
                        <a href="https://www.google.com/maps/search/?api=1&query={{ $pelanggan->latitude }},{{ $pelanggan->longitude }}" target="_blank" style="margin-left: 8px; color: #3b82f6; text-decoration: underline;" title="Buka di Google Maps">
                            <i class="fas fa-map-marker-alt"></i> Peta
                        </a>
                    @else
                        <span class="mono-mute">—</span>
                    @endif
                </span>
            </div>

            {{-- Divider --}}
            <div style="margin:14px 0 10px;font-size:10px;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:var(--text-4);">Layanan & Jaringan</div>

            <div class="info-row">
                <span class="key">Paket Layanan</span>
                <span class="val" style="display:flex;align-items:center;gap:8px;">
                    @if($pelanggan->paket)
                        <span style="color:var(--text-1);font-family:inherit;font-size:13px;">{{ $pelanggan->paket->nama }}</span>
                        <span class="mono-mute">{{ $pelanggan->paket->kecepatan_down }}↓/{{ $pelanggan->paket->kecepatan_up }}↑ Mbps</span>
                    @else
                        <span>—</span>
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="key">NAS / Router</span>
                <span class="val">
                    @if($pelanggan->nas)
                        {{ $pelanggan->nas->nama }}
                        <span class="mono-mute" style="margin-left:4px;">({{ $pelanggan->nas->ip_address }})</span>
                    @else
                        —
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="key">IP Address</span>
                <span class="val">
                    @if($pelanggan->ip_address)
                        <span id="ls-ip-address" class="mono" style="color:#7dd3fc;">{{ $pelanggan->ip_address }}</span>
                    @else
                        <span id="ls-ip-address" class="mono-mute">—</span>
                    @endif
                </span>
            </div>

            {{-- Divider --}}
            <div style="margin:14px 0 10px;font-size:10px;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:var(--text-4);">Status & Waktu</div>

            <div class="info-row">
                <span class="key">Status</span>
                <span class="val">{!! $pelanggan->status_badge !!}</span>
            </div>
            <div class="info-row">
                <span class="key">Tanggal Aktif</span>
                <span class="val mono">
                    {{ $pelanggan->tgl_aktif?->format('d M Y') ?? '—' }}
                </span>
            </div>
            <div class="info-row">
                <span class="key">Jatuh Tempo</span>
                <span class="val">
                    @if($pelanggan->expiry)
                        @php $expired = $pelanggan->expiry->isPast(); @endphp
                        <span class="mono" style="color: {{ $expired ? 'var(--red)' : ($pelanggan->expiry->diffInDays(now()) <= 7 ? 'var(--amber)' : 'var(--text-2)') }};">
                            {{ $pelanggan->expiry->format('d M Y') }}
                        </span>
                        @if($expired)
                            <span style="font-size:10px;color:var(--red);margin-left:6px;">(Sudah lewat)</span>
                        @elseif($pelanggan->expiry->diffInDays(now()) <= 7)
                            <span style="font-size:10px;color:var(--amber);margin-left:6px;">({{ $pelanggan->expiry->diffInDays(now()) }} hari lagi)</span>
                        @endif
                    @else
                        <span class="mono-mute">—</span>
                    @endif
                </span>
            </div>

            @if($pelanggan->isolir_at || $pelanggan->isolir_by)
            <div class="info-row">
                <span class="key">Diisolir Oleh</span>
                <span class="val mono-mute">{{ $pelanggan->isolir_by ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="key">Waktu Isolir</span>
                <span class="val mono-mute">
                    {{ $pelanggan->isolir_at?->format('d M Y H:i') ?? '—' }}
                </span>
            </div>
            @endif
        </div>
    </div>

    {{-- ── RIGHT: Live Session Status ── --}}
    <div class="card" id="live-session-widget">
        <div class="card-header" style="border-bottom:none; padding-bottom:0;">
            <div>
                <div class="card-title"><i class="fas fa-shield-halved" style="margin-right:6px;color:var(--sky);"></i>Status Koneksi (Live)</div>
                <div class="card-subtitle">Data real-time dari MikroTik</div>
            </div>
            <span id="ls-badge" class="badge badge-inactive">Loading...</span>
        </div>
        <div class="card-body">
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                <div style="background:var(--bg-2); border:1px solid var(--border); border-radius:8px; padding:12px; text-align:center;">
                    <div style="font-size:10px; font-weight:600; color:var(--text-4); letter-spacing:1px; margin-bottom:4px;">UPTIME</div>
                    <div id="ls-uptime" class="mono" style="font-size:15px; font-weight:700; color:var(--text-1);">--</div>
                </div>
                <div style="background:var(--bg-2); border:1px solid var(--border); border-radius:8px; padding:12px; text-align:center;">
                    <div style="font-size:10px; font-weight:600; color:var(--text-4); letter-spacing:1px; margin-bottom:4px;">RATE</div>
                    <div id="ls-rate" class="mono-mute" style="font-size:13px; font-weight:600; color:var(--text-2); margin-top:2px;">--</div>
                </div>
                <div style="background:var(--bg-2); border:1px solid var(--border); border-radius:8px; padding:12px; text-align:center;">
                    <div style="font-size:10px; font-weight:600; color:var(--text-4); letter-spacing:1px; margin-bottom:4px;">DOWNLOAD</div>
                    <div id="ls-download" class="mono" style="font-size:15px; font-weight:700; color:var(--green);">--</div>
                </div>
                <div style="background:var(--bg-2); border:1px solid var(--border); border-radius:8px; padding:12px; text-align:center;">
                    <div style="font-size:10px; font-weight:600; color:var(--text-4); letter-spacing:1px; margin-bottom:4px;">UPLOAD</div>
                    <div id="ls-upload" class="mono" style="font-size:15px; font-weight:700; color:var(--sky);">--</div>
                </div>
            </div>
            <div style="font-size:11px; color:var(--text-4); text-align:center; margin-top:16px;">
                <i class="fas fa-rotate" style="margin-right:4px;"></i> Auto refresh setiap 1 detik
            </div>
        </div>
    </div>
    
    <script>
        setInterval(() => {
            fetch('{{ route('pelanggan.liveSession', $pelanggan->id) }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                let badge = document.getElementById('ls-badge');
                if (data.status === 'online') {
                    badge.className = 'badge badge-online';
                    badge.innerHTML = '<i class="fas fa-circle" style="font-size:6px;"></i> Online';
                } else {
                    badge.className = 'badge badge-offline';
                    badge.innerHTML = '<i class="fas fa-circle" style="font-size:6px;"></i> Offline';
                }
                document.getElementById('ls-uptime').innerText = data.uptime;
                document.getElementById('ls-download').innerText = data.download;
                document.getElementById('ls-upload').innerText = data.upload;
                document.getElementById('ls-rate').innerText = data.rate;
                
                if (data.status === 'online' && data.ip_address !== '-') {
                    document.getElementById('ls-ip-address').innerText = data.ip_address;
                    document.getElementById('ls-ip-address').style.color = 'var(--text-1)';
                } else {
                    document.getElementById('ls-ip-address').innerText = '{{ $pelanggan->ip_address ?? '—' }}';
                    document.getElementById('ls-ip-address').style.color = '';
                }
            })
            .catch(err => console.error("Error fetching live session", err));
        }, 1000);
    </script>
    </div>

</div>

{{-- ═══════════════════════════════════════════════
     FULL-WIDTH: RECENT INVOICES
═══════════════════════════════════════════════ --}}
<div class="card" style="margin-bottom:16px;">
    <div class="card-header">
        <div>
            <div class="card-title"><i class="fas fa-file-invoice-dollar" style="margin-right:6px;color:var(--amber);"></i>Invoice Terbaru</div>
            <div class="card-subtitle">10 invoice terakhir pelanggan ini</div>
        </div>
        @if(auth()->user()->hasRole(['admin','kasir']))
        <a href="{{ route('invoice.index') }}?pelanggan={{ $pelanggan->id }}" class="btn btn-ghost btn-sm">
            <i class="fas fa-external-link"></i> Lihat Semua
        </a>
        @endif
    </div>
    <div class="card-body-flush">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>No. Invoice</th>
                        <th>Periode</th>
                        <th>Nominal</th>
                        <th>Status</th>
                        <th>Jatuh Tempo</th>
                        <th>Tgl Bayar</th>
                        @if(auth()->user()->hasRole(['admin','kasir']))
                        <th style="text-align:right;">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($pelanggan->invoices as $i => $inv)
                    <tr>
                        <td class="mono-mute">{{ $i + 1 }}</td>
                        <td>
                            <span class="mono" style="color:#a5b4fc;">{{ $inv->no_invoice }}</span>
                        </td>
                        <td>
                            <span class="mono-mute">{{ $inv->periode }}</span>
                        </td>
                        <td>
                            <span class="mono" style="color:var(--text-1);">
                                Rp {{ number_format($inv->nominal, 0, ',', '.') }}
                            </span>
                        </td>
                        <td>
                            @php
                                $statusClass = match($inv->status) {
                                    'paid'    => 'badge-paid',
                                    'unpaid'  => 'badge-unpaid',
                                    'partial' => 'badge-partial',
                                    default   => 'badge',
                                };
                                $statusLabel = match($inv->status) {
                                    'paid'    => 'Lunas',
                                    'unpaid'  => 'Belum Bayar',
                                    'partial' => 'Sebagian',
                                    default   => $inv->status,
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                            @if($inv->isOverdue())
                                <span style="font-size:10px;color:var(--red);display:block;margin-top:2px;">Jatuh tempo!</span>
                            @endif
                        </td>
                        <td>
                            @if($inv->tgl_jatuh_tempo)
                                <span class="mono-mute" style="{{ $inv->isOverdue() ? 'color:var(--red);' : '' }}">
                                    {{ $inv->tgl_jatuh_tempo->format('d M Y') }}
                                </span>
                            @else
                                <span class="mono-mute">—</span>
                            @endif
                        </td>
                        <td>
                            @if($inv->tgl_bayar)
                                <span class="mono-mute" style="color:var(--green);">{{ $inv->tgl_bayar->format('d M Y') }}</span>
                            @else
                                <span class="mono-mute">—</span>
                            @endif
                        </td>
                        @if(auth()->user()->hasRole(['admin','kasir']))
                        <td style="text-align:right;">
                            <a href="{{ route('invoice.show', $inv->id) }}" class="btn btn-ghost btn-xs">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->hasRole(['admin','kasir']) ? 8 : 7 }}">
                            <div class="empty-state" style="padding:28px;">
                                <i class="fas fa-file-invoice"></i>
                                <h3>Belum ada invoice</h3>
                                <p>Belum ada invoice yang dibuat untuk pelanggan ini.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════
     FULL-WIDTH: ISOLIR LOG
═══════════════════════════════════════════════ --}}
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title"><i class="fas fa-clock-rotate-left" style="margin-right:6px;color:var(--red);"></i>Riwayat Isolir</div>
            <div class="card-subtitle">10 log terakhir aksi isolir / aktifasi</div>
        </div>
    </div>
    <div class="card-body-flush">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Aksi</th>
                        <th>Metode</th>
                        <th>Oleh</th>
                        <th>Alasan</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pelanggan->isolirLogs as $i => $log)
                    <tr>
                        <td class="mono-mute">{{ $i + 1 }}</td>
                        <td>
                            @if($log->aksi === 'isolir')
                                <span style="display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:600;color:#fcd34d;">
                                    <i class="fas fa-lock" style="font-size:10px;"></i> Isolir
                                </span>
                            @elseif($log->aksi === 'aktifkan')
                                <span style="display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:600;color:#6ee7b7;">
                                    <i class="fas fa-lock-open" style="font-size:10px;"></i> Aktifkan
                                </span>
                            @else
                                <span class="mono-mute">{{ $log->aksi }}</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $metodeBadge = match($log->metode) {
                                    'manual' => 'badge-manual',
                                    'auto'   => 'badge-auto',
                                    default  => 'badge',
                                };
                            @endphp
                            <span class="badge {{ $metodeBadge }}">{{ ucfirst($log->metode ?? '—') }}</span>
                        </td>
                        <td>
                            @if($log->user)
                                <div style="font-size:13px;color:var(--text-1);">{{ $log->user->name }}</div>
                                <div style="font-size:10px;color:var(--text-4);">{{ strtoupper($log->user->role ?? '') }}</div>
                            @else
                                <span class="mono-mute">System</span>
                            @endif
                        </td>
                        <td style="max-width:240px;">
                            <span style="font-size:12px;color:var(--text-3);">{{ $log->alasan ?? '—' }}</span>
                        </td>
                        <td>
                            <div class="mono-mute" style="font-size:11px;">
                                {{ $log->created_at?->format('d M Y') }}
                            </div>
                            <div class="mono-mute" style="font-size:10px;color:var(--text-4);">
                                {{ $log->created_at?->format('H:i:s') }}
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state" style="padding:28px;">
                                <i class="fas fa-clock-rotate-left"></i>
                                <h3>Belum ada riwayat</h3>
                                <p>Tidak ada riwayat isolir untuk pelanggan ini.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
