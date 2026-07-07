@extends('layouts.app')
@section('title', 'Pengaturan Sistem')
@section('page-title', 'Pengaturan Sistem')
@section('breadcrumb', 'Sistem / Pengaturan')

@section('content')

<style>
.settings-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 24px;
}

@media (max-width: 800px) {
    .settings-grid { grid-template-columns: 1fr; }
}

.info-box {
    background: var(--bg-base);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 12px 14px;
    margin-top: 4px;
}

.info-box-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 5px 0;
    border-bottom: 1px solid rgba(51,65,85,0.4);
}

.info-box-row:last-child { border-bottom: none; }

.info-box-label {
    font-size: 11px;
    color: var(--text-4);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}

.info-box-val {
    font-size: 12px;
    font-family: 'JetBrains Mono', monospace;
    color: var(--text-2);
}

.toggle-wrap {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    background: var(--bg-base);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    cursor: pointer;
    user-select: none;
    transition: border-color var(--transition);
}

.toggle-wrap:hover { border-color: var(--border-light); }

.toggle-switch {
    position: relative;
    width: 40px;
    height: 22px;
    flex-shrink: 0;
}

.toggle-switch input {
    opacity: 0;
    width: 0; height: 0;
    position: absolute;
}

.toggle-slider {
    position: absolute;
    inset: 0;
    background: var(--bg-elevated);
    border-radius: 100px;
    cursor: pointer;
    transition: background 0.2s ease;
    border: 1px solid var(--border-light);
}

.toggle-slider::before {
    content: '';
    position: absolute;
    width: 16px; height: 16px;
    left: 2px; top: 2px;
    background: var(--text-3);
    border-radius: 50%;
    transition: transform 0.2s ease, background 0.2s ease;
}

.toggle-switch input:checked + .toggle-slider {
    background: var(--indigo);
    border-color: var(--indigo);
}

.toggle-switch input:checked + .toggle-slider::before {
    transform: translateX(18px);
    background: white;
}

.toggle-text-wrap { flex: 1; }

.toggle-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-1);
}

.toggle-desc {
    font-size: 11px;
    color: var(--text-4);
    margin-top: 2px;
}

.bank-field-icon {
    width: 32px; height: 32px;
    border-radius: var(--radius-sm);
    display: flex; align-items: center; justify-content: center;
    font-size: 13px;
    flex-shrink: 0;
    margin-right: 4px;
}

.bank-input-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 14px;
}

.bank-label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-3);
    margin-bottom: 5px;
}

.alert-info {
    background: var(--sky-d);
    border: 1px solid rgba(14,165,233,0.25);
    border-radius: var(--radius);
    padding: 10px 14px;
    font-size: 12px;
    color: #7dd3fc;
    display: flex;
    align-items: flex-start;
    gap: 8px;
    margin-bottom: 16px;
}
</style>

<div class="page-header">
    <div class="page-header-title">
        <h1>Pengaturan Sistem</h1>
        <p>Konfigurasi auto-isolir, rekening bank, dan preferensi sistem</p>
    </div>
</div>

@if(session('success'))
<div style="background:var(--green-d); border:1px solid rgba(16,185,129,0.3); border-radius:var(--radius); padding:10px 16px; margin-bottom:16px; color:#6ee7b7; font-size:13px; display:flex; align-items:center; gap:8px;">
    <i class="fas fa-circle-check"></i>
    {{ session('success') }}
</div>
@endif

<div class="settings-grid">
    {{-- LEFT: Auto-Isolir Config --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title"><i class="fas fa-robot" style="color:var(--indigo); margin-right:6px;"></i>Konfigurasi Auto-Isolir</div>
                <div class="card-subtitle">Atur jadwal dan parameter isolir otomatis</div>
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('pengaturan.update') }}">
                @csrf

                {{-- Toggle Active --}}
                <div class="form-group">
                    <label class="toggle-wrap" for="toggle-isolir">
                        <label class="toggle-switch">
                            <input type="checkbox" id="toggle-isolir" name="auto_isolir_enabled" value="1"
                                   {{ !empty($settings['auto_isolir_enabled']) ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                        <div class="toggle-text-wrap">
                            <div class="toggle-title">Auto-Isolir Aktif</div>
                            <div class="toggle-desc">Isolir otomatis pelanggan yang melewati jatuh tempo</div>
                        </div>
                    </label>
                </div>

                {{-- Grace Period --}}
                <div class="form-group">
                    <label class="form-label">Grace Period (Hari)</label>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <input type="number" name="grace_period_days" class="form-control form-control-mono"
                               min="0" max="30"
                               value="{{ old('grace_period_days', $settings['grace_period_days'] ?? 3) }}"
                               style="width:120px;">
                        <span style="font-size:12px; color:var(--text-3);">hari setelah jatuh tempo</span>
                    </div>
                    @if($errors->first('grace_period'))
                        <div class="form-error">{{ $errors->first('grace_period') }}</div>
                    @endif
                    <div class="form-hint">Rentang: 0–30 hari. 0 berarti isolir langsung pada hari jatuh tempo.</div>
                </div>

                {{-- Jam Eksekusi --}}
                <div class="form-group">
                    <label class="form-label">Jam Eksekusi</label>
                    <input type="time" name="isolir_time" class="form-control form-control-mono"
                           value="{{ old('isolir_time', $settings['isolir_time'] ?? '08:00') }}"
                           style="width:160px;">
                    @if($errors->first('jam_eksekusi'))
                        <div class="form-error">{{ $errors->first('jam_eksekusi') }}</div>
                    @endif
                    <div class="form-hint">Waktu (WIB) proses auto-isolir dijalankan setiap harinya.</div>
                </div>

                {{-- Info Box --}}
                <div class="form-group">
                    <label class="form-label">Riwayat Eksekusi Terakhir</label>
                    <div class="info-box">
                        <div class="info-box-row">
                            <span class="info-box-label">Terakhir Dijalankan</span>
                            <span class="info-box-val">
                                @php
                                    $lastRun = $settings['auto_isolir_last_run'] ?? null;
                                    // Pastikan bukan JSON/object, harus string datetime
                                    $lastRunStr = ($lastRun && is_string($lastRun) && !str_starts_with($lastRun, '{'))
                                        ? \Carbon\Carbon::parse($lastRun)->format('d/m/Y H:i')
                                        : '—';
                                @endphp
                                {{ $lastRunStr }}
                            </span>
                        </div>
                        <div class="info-box-row">
                            <span class="info-box-label">Jumlah Diisolir</span>
                            <span class="info-box-val" style="color:var(--amber);">
                                {{ $settings['auto_isolir_last_count'] ?? 0 }} pelanggan
                            </span>
                        </div>
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; margin-top:4px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- RIGHT: Info Rekening Bank --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title"><i class="fas fa-building-columns" style="color:var(--green); margin-right:6px;"></i>Info Rekening Bank</div>
                <div class="card-subtitle">Nomor rekening untuk konfirmasi pembayaran</div>
            </div>
        </div>
        <div class="card-body">
            <div class="alert-info">
                <i class="fas fa-circle-info" style="margin-top:1px; flex-shrink:0;"></i>
                <span>Nomor rekening ini ditampilkan kepada kasir saat konfirmasi pembayaran dari pelanggan.</span>
            </div>

            <form method="POST" action="{{ route('pengaturan.update') }}">
                @csrf

                {{-- BCA --}}
                <div class="form-group">
                    <label class="bank-label">Bank BCA</label>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div class="bank-field-icon" style="background:rgba(0,86,179,0.15); color:#60a5fa;">
                            <i class="fas fa-landmark"></i>
                        </div>
                        <input type="text" name="bank_bca" class="form-control form-control-mono"
                               placeholder="1234567890"
                               value="{{ old('bank_bca', $settings['bank_bca'] ?? '') }}">
                    </div>
                    @if($errors->first('bank_bca'))
                        <div class="form-error">{{ $errors->first('bank_bca') }}</div>
                    @endif
                </div>

                {{-- BRI --}}
                <div class="form-group">
                    <label class="bank-label">Bank BRI</label>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div class="bank-field-icon" style="background:rgba(0,100,0,0.15); color:#4ade80;">
                            <i class="fas fa-landmark"></i>
                        </div>
                        <input type="text" name="bank_bri" class="form-control form-control-mono"
                               placeholder="1234567890"
                               value="{{ old('bank_bri', $settings['bank_bri'] ?? '') }}">
                    </div>
                    @if($errors->first('bank_bri'))
                        <div class="form-error">{{ $errors->first('bank_bri') }}</div>
                    @endif
                </div>

                {{-- Mandiri --}}
                <div class="form-group">
                    <label class="bank-label">Bank Mandiri</label>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div class="bank-field-icon" style="background:rgba(245,158,11,0.15); color:#fcd34d;">
                            <i class="fas fa-landmark"></i>
                        </div>
                        <input type="text" name="bank_mandiri" class="form-control form-control-mono"
                               placeholder="1234567890"
                               value="{{ old('bank_mandiri', $settings['bank_mandiri'] ?? '') }}">
                    </div>
                    @if($errors->first('bank_mandiri'))
                        <div class="form-error">{{ $errors->first('bank_mandiri') }}</div>
                    @endif
                </div>

                {{-- BNI --}}
                <div class="form-group">
                    <label class="bank-label">Bank BNI</label>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div class="bank-field-icon" style="background:rgba(239,68,68,0.12); color:#fca5a5;">
                            <i class="fas fa-landmark"></i>
                        </div>
                        <input type="text" name="bank_bni" class="form-control form-control-mono"
                               placeholder="1234567890"
                               value="{{ old('bank_bni', $settings['bank_bni'] ?? '') }}">
                    </div>
                    @if($errors->first('bank_bni'))
                        <div class="form-error">{{ $errors->first('bank_bni') }}</div>
                    @endif
                </div>

                <div style="display:flex; justify-content:flex-end; margin-top:8px;">
                    <button type="submit" name="section" value="bank" class="btn btn-success">
                        <i class="fas fa-save"></i> Simpan Rekening
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Recent Auto-Isolir Log Table --}}
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title"><i class="fas fa-clock-rotate-left" style="color:var(--amber); margin-right:6px;"></i>Log Auto-Isolir Terbaru</div>
            <div class="card-subtitle">Riwayat pelanggan yang diisolir otomatis</div>
        </div>
    </div>
    <div class="card-body-flush">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Pelanggan</th>
                        <th>Metode</th>
                        <th>Alasan</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentLogs ?? [] as $log)
                    <tr>
                        <td>
                            @if(isset($log->pelanggan))
                                <div style="font-weight:500; color:var(--text-1);">{{ $log->pelanggan->nama ?? '-' }}</div>
                                <div class="mono-mute" style="font-size:10px;">{{ $log->pelanggan->kode ?? '' }}</div>
                            @else
                                <span class="mono-mute">{{ $log->pelanggan_nama ?? $log->pelanggan_id ?? '-' }}</span>
                            @endif
                        </td>
                        <td>
                            @if(($log->metode ?? '') === 'auto')
                                <span class="badge badge-auto">Auto</span>
                            @else
                                <span class="badge badge-manual">Manual</span>
                            @endif
                        </td>
                        <td>
                            <span style="font-size:12px; color:var(--text-2);">{{ $log->alasan ?? $log->reason ?? '-' }}</span>
                        </td>
                        <td>
                            <div style="font-size:12px; color:var(--text-2);">
                                {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i') }}
                            </div>
                            <div style="font-size:10px; color:var(--text-4);">
                                {{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4">
                            <div style="text-align:center; padding:36px 20px; color:var(--text-4);">
                                <i class="fas fa-clock-rotate-left" style="font-size:30px; margin-bottom:10px; display:block; color:var(--border-light);"></i>
                                <div style="font-weight:600; color:var(--text-3); margin-bottom:4px;">Belum Ada Log</div>
                                <div style="font-size:12px;">Auto-isolir belum pernah dijalankan atau tidak ada pelanggan yang diisolir.</div>
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
