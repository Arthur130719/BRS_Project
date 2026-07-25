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

            <form method="POST" action="{{ route('pengaturan.update') }}" id="formRekening">
                @csrf

                @php
                    $rekeningBanks = [];
                    if (isset($settings['rekening_banks'])) {
                        $rekeningBanks = json_decode($settings['rekening_banks'], true) ?: [];
                    } else {
                        if(!empty($settings['bank_bca'])) {
                            $p = explode(' a.n ', $settings['bank_bca']);
                            $rekeningBanks[] = ['bank'=>'BCA', 'norek'=> trim($p[0]), 'an'=> trim($p[1] ?? '')];
                        }
                        if(!empty($settings['bank_bri'])) {
                            $p = explode(' a.n ', $settings['bank_bri']);
                            $rekeningBanks[] = ['bank'=>'BRI', 'norek'=> trim($p[0]), 'an'=> trim($p[1] ?? '')];
                        }
                        if(!empty($settings['bank_mandiri'])) {
                            $p = explode(' a.n ', $settings['bank_mandiri']);
                            $rekeningBanks[] = ['bank'=>'Mandiri', 'norek'=> trim($p[0]), 'an'=> trim($p[1] ?? '')];
                        }
                        if(!empty($settings['bank_bni'])) {
                            $p = explode(' a.n ', $settings['bank_bni']);
                            $rekeningBanks[] = ['bank'=>'BNI', 'norek'=> trim($p[0]), 'an'=> trim($p[1] ?? '')];
                        }
                    }
                @endphp

                <div id="rekening-list" style="display:flex; flex-direction:column; gap:16px;">
                    <!-- Di-render oleh JS -->
                </div>

                <div style="margin-top:16px; border-top:1px dashed var(--border); padding-top:16px; display:flex; justify-content:space-between; align-items:center;">
                    <button type="button" class="btn btn-outline" style="font-size:12px; padding:6px 12px;" onclick="addRekening()">
                        <i class="fas fa-plus"></i> Tambah Rekening
                    </button>
                    <button type="submit" name="section" value="bank" class="btn btn-success">
                        <i class="fas fa-save"></i> Simpan Rekening
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let rekeningData = @json($rekeningBanks);
    const rekeningList = document.getElementById('rekening-list');

    function renderRekening() {
        rekeningList.innerHTML = '';
        if(rekeningData.length === 0) {
            rekeningList.innerHTML = '<div style="text-align:center; padding:20px; color:var(--text-4); font-size:12px;">Belum ada rekening. Klik Tambah Rekening.</div>';
            return;
        }

        rekeningData.forEach((rek, index) => {
            const div = document.createElement('div');
            div.style.cssText = 'background:var(--bg-base); padding:12px; border:1px solid var(--border); border-radius:var(--radius); position:relative;';
            div.innerHTML = `
                <div style="position:absolute; top:8px; right:8px;">
                    <button type="button" onclick="removeRekening(${index})" style="background:var(--red-d); border:1px solid rgba(239,68,68,0.2); color:#fca5a5; width:28px; height:28px; border-radius:6px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:0.2s;"><i class="fas fa-trash"></i></button>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1.5fr; gap:12px; margin-bottom:8px; padding-right:32px;">
                    <div>
                        <label style="font-size:10px; font-weight:600; text-transform:uppercase; color:var(--text-3); margin-bottom:4px; display:block;">Nama Bank/E-Wallet</label>
                        <input type="text" name="rekening[${index}][bank]" class="form-control" value="${rek.bank || ''}" placeholder="Cth: BCA / DANA" required>
                    </div>
                    <div>
                        <label style="font-size:10px; font-weight:600; text-transform:uppercase; color:var(--text-3); margin-bottom:4px; display:block;">Nomor Rekening</label>
                        <input type="text" name="rekening[${index}][norek]" class="form-control form-control-mono" value="${rek.norek || ''}" placeholder="123456789" required>
                    </div>
                </div>
                <div>
                    <label style="font-size:10px; font-weight:600; text-transform:uppercase; color:var(--text-3); margin-bottom:4px; display:block;">Atas Nama</label>
                    <input type="text" name="rekening[${index}][an]" class="form-control" value="${rek.an || ''}" placeholder="Nama Pemilik" required>
                </div>
            `;
            rekeningList.appendChild(div);
        });
    }

    function addRekening() {
        rekeningData.push({ bank: '', norek: '', an: '' });
        renderRekening();
    }

    function removeRekening(index) {
        if(confirm('Hapus rekening ini?')) {
            rekeningData.splice(index, 1);
            renderRekening();
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        renderRekening();
    });
</script>

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
