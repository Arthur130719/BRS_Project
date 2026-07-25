@extends('layouts.app')
@section('title', 'Catat Pembayaran')
@section('page-title', 'Keuangan')
@section('breadcrumb', 'Catat Pembayaran')

@section('content')
<div
    x-data="{
        selectedInvoice: null,
        invoices: {{ Js::from($invoices ?? []) }},
        metode: '{{ old('metode', 'cash') }}',
        showNamaBank: false,

        selectInvoice(id) {
            if (!id) { this.selectedInvoice = null; return; }
            this.selectedInvoice = this.invoices.find(i => i.id == id) || null;
        },
        get isTransfer() {
            return this.metode && this.metode !== 'cash';
        },
        get isTransferLain() {
            return this.metode === 'Transfer Lain';
        },
        get metodeLabelMap() {
            return {
                'cash': 'Cash',
                // Any other method will fallback or can be mapped dynamically if needed.
            };
        }
    }"
>

{{-- ── Page Header ── --}}
<div class="page-header">
    <div class="page-header-title">
        <h1><i class="fas fa-money-bill-transfer" style="color:var(--green);margin-right:8px;"></i>Catat Pembayaran</h1>
        <p>Rekam pembayaran tagihan pelanggan secara manual</p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('pembayaran.index') }}" class="btn btn-ghost">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

@if($errors->any())
<div class="alert alert-danger" style="margin-bottom:16px;">
    <i class="fas fa-circle-exclamation"></i>
    <div>
        <strong>Terdapat {{ $errors->count() }} kesalahan:</strong>
        @foreach($errors->all() as $err)
            <div style="margin-top:2px;">• {{ $err }}</div>
        @endforeach
    </div>
</div>
@endif

<div style="display:grid;grid-template-columns:1fr 380px;gap:20px;align-items:start;">

    {{-- ══════════════════════════════
         LEFT — FORM
    ═══════════════════════════════════ --}}
    <div>
        <form method="POST" action="{{ route('pembayaran.store') }}" enctype="multipart/form-data" id="formPembayaran">
            @csrf

            {{-- ── Invoice Selection ── --}}
            <div class="card" style="margin-bottom:16px;">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-file-invoice-dollar" style="color:var(--indigo);margin-right:6px;"></i>Pilih Invoice</div>
                </div>
                <div class="card-body">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Invoice <span style="color:var(--red);">*</span></label>
                        <select name="invoice_id"
                                class="form-control form-control-mono"
                                @change="selectInvoice($event.target.value)"
                                required>
                            <option value="">— Pilih Invoice —</option>
                            @foreach($invoices ?? [] as $inv)
                                <option value="{{ $inv['id'] }}"
                                        {{ old('invoice_id') == $inv['id'] ? 'selected' : '' }}>
                                    {{ $inv['no_invoice'] }} — {{ $inv['pelanggan_nama'] }}
                                    (Rp {{ number_format($inv['nominal'], 0, ',', '.') }})
                                    @if($inv['status'] === 'partial') [Sebagian] @endif
                                </option>
                            @endforeach
                        </select>
                        @if($errors->has('invoice_id'))
                            <div class="form-error">{{ $errors->first('invoice_id') }}</div>
                        @endif
                        <div class="form-hint">Pilih invoice yang akan dibayar</div>
                    </div>
                </div>
            </div>

            {{-- ── Metode Pembayaran ── --}}
            <div class="card" style="margin-bottom:16px;">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-credit-card" style="color:var(--sky);margin-right:6px;"></i>Metode Pembayaran</div>
                </div>
                <div class="card-body">

                    {{-- Metode Radio Cards --}}
                    <div class="form-group">
                        <label class="form-label">Metode <span style="color:var(--red);">*</span></label>
                        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;">

                            @php
                                $rekeningBanks = json_decode(\App\Models\SystemSetting::get('rekening_banks', '[]'), true);
                                $metodeOptions = [
                                    'cash' => ['icon' => 'fas fa-money-bill-wave', 'label' => 'Cash', 'color' => 'var(--green)'],
                                ];
                                foreach($rekeningBanks as $rek) {
                                    $bankName = strtolower($rek['bank'] ?? '');
                                    $bank = $rek['bank'] ?? 'Bank';
                                    $an = $rek['an'] ?? '';
                                    
                                    $label = "Transfer $bank" . ($an ? " (a.n $an)" : "");
                                    $val = $label;
                                    
                                    $color = 'var(--sky)';
                                    if (str_contains($bankName, 'bca') || str_contains($bankName, 'bri')) $color = '#00529C';
                                    if (str_contains($bankName, 'mandiri')) $color = '#F2A900';
                                    if (str_contains($bankName, 'bni')) $color = '#F15A23';
                                    if (str_contains($bankName, 'dana') || str_contains($bankName, 'ovo') || str_contains($bankName, 'gopay')) $color = '#118EEA';
                                    
                                    $icon = (str_contains($bankName, 'dana') || str_contains($bankName, 'ovo') || str_contains($bankName, 'gopay')) ? 'fas fa-wallet' : 'fas fa-building-columns';
                                    
                                    $metodeOptions[$val] = ['icon' => $icon, 'label' => $label, 'color' => $color];
                                }
                                $metodeOptions['Transfer Lain'] = ['icon' => 'fas fa-ellipsis', 'label' => 'Transfer Lain', 'color' => 'var(--text-2)'];
                            @endphp

                            @foreach($metodeOptions as $val => $opt)
                            <label
                                style="cursor:pointer;border-radius:var(--radius);border:2px solid;padding:10px 8px;text-align:center;transition:all 0.15s;display:flex;flex-direction:column;align-items:center;gap:4px;"
                                :style="metode === '{{ $val }}'
                                    ? 'border-color:var(--indigo);background:var(--indigo-glow);'
                                    : 'border-color:var(--border);background:var(--bg-input);'"
                            >
                                <input type="radio"
                                       name="metode"
                                       value="{{ $val }}"
                                       x-model="metode"
                                       style="position:absolute;opacity:0;width:0;height:0;"
                                       {{ old('metode', 'cash') === $val ? 'checked' : '' }}>
                                <i class="{{ $opt['icon'] }}"
                                   style="font-size:18px;"
                                   :style="metode === '{{ $val }}' ? 'color:var(--indigo);' : 'color:{{ $opt['color'] }};'"></i>
                                <span style="font-size:11px;font-weight:500;"
                                      :style="metode === '{{ $val }}' ? 'color:var(--text-1);' : 'color:var(--text-3);'">
                                    {{ $opt['label'] }}
                                </span>
                            </label>
                            @endforeach
                        </div>
                        @if($errors->has('metode'))
                            <div class="form-error">{{ $errors->first('metode') }}</div>
                        @endif
                    </div>

                    {{-- Nama Bank (hanya jika transfer_lain) --}}
                    <div x-show="isTransferLain" x-transition style="display:none;" class="form-group">
                        <label class="form-label">Nama Bank <span style="color:var(--red);">*</span></label>
                        <input type="text"
                               name="nama_bank"
                               class="form-control"
                               placeholder="mis. Danamon, BTN, dll."
                               value="{{ old('nama_bank') }}"
                               :required="isTransferLain">
                        @if($errors->has('nama_bank'))
                            <div class="form-error">{{ $errors->first('nama_bank') }}</div>
                        @endif
                    </div>

                    {{-- Tgl Bayar & Nominal --}}
                    <div class="form-row">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Tanggal Bayar <span style="color:var(--red);">*</span></label>
                            <input type="date"
                                   name="tgl_bayar"
                                   class="form-control form-control-mono"
                                   value="{{ old('tgl_bayar', now()->format('Y-m-d')) }}"
                                   required>
                            @if($errors->has('tgl_bayar'))
                                <div class="form-error">{{ $errors->first('tgl_bayar') }}</div>
                            @endif
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Nominal (Rp) <span style="color:var(--red);">*</span></label>
                            <input type="number"
                                   name="nominal"
                                   id="inputNominal"
                                   class="form-control form-control-mono"
                                   placeholder="mis. 150000"
                                   value="{{ old('nominal') }}"
                                   min="1"
                                   required>
                            @if($errors->has('nominal'))
                                <div class="form-error">{{ $errors->first('nominal') }}</div>
                            @endif
                            <div class="form-hint">
                                <span x-show="selectedInvoice" x-text="selectedInvoice ? 'Tagihan: Rp ' + Number(selectedInvoice.nominal).toLocaleString('id-ID') : ''"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Keterangan & Bukti ── --}}
            <div class="card" style="margin-bottom:16px;">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-paperclip" style="color:var(--amber);margin-right:6px;"></i>Keterangan & Bukti</div>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan"
                                  class="form-control"
                                  rows="2"
                                  placeholder="Catatan tambahan (opsional)">{{ old('keterangan') }}</textarea>
                        @if($errors->has('keterangan'))
                            <div class="form-error">{{ $errors->first('keterangan') }}</div>
                        @endif
                    </div>

                    {{-- Bukti Transfer (tampil jika bukan cash) --}}
                    <div x-show="isTransfer" x-transition style="display:none;" class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Bukti Transfer</label>
                        <div style="border:2px dashed var(--border);border-radius:var(--radius);padding:16px;text-align:center;background:var(--bg-input);transition:border-color 0.15s;"
                             @dragover.prevent="$el.style.borderColor='var(--indigo)'"
                             @dragleave="$el.style.borderColor='var(--border)'"
                             @drop.prevent="$el.style.borderColor='var(--border)'">
                            <i class="fas fa-cloud-arrow-up" style="font-size:24px;color:var(--text-4);margin-bottom:8px;"></i>
                            <div style="font-size:12px;color:var(--text-3);margin-bottom:8px;">Upload foto/bukti transfer</div>
                            <input type="file"
                                   name="bukti_transfer"
                                   id="buktiTransfer"
                                   accept="image/*,.pdf"
                                   style="display:none;"
                                   @change="
                                       const f = $event.target.files[0];
                                       if(f) $refs.buktiName.textContent = f.name;
                                   ">
                            <label for="buktiTransfer" class="btn btn-ghost btn-sm" style="cursor:pointer;">
                                <i class="fas fa-folder-open"></i> Pilih File
                            </label>
                            <div x-ref="buktiName" class="mono-mute" style="margin-top:6px;font-size:11px;"></div>
                        </div>
                        <div class="form-hint">Format: JPG, JPEG, PNG, PDF — Maks. 10MB</div>
                        @if($errors->has('bukti_transfer'))
                            <div class="form-error">{{ $errors->first('bukti_transfer') }}</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ── Submit ── --}}
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <a href="{{ route('pembayaran.index') }}" class="btn btn-ghost">
                    <i class="fas fa-xmark"></i> Batal
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Catat Pembayaran
                </button>
            </div>
        </form>
    </div>

    {{-- ══════════════════════════════
         RIGHT — INVOICE PREVIEW
    ═══════════════════════════════════ --}}
    <div>
        {{-- Empty state --}}
        <div x-show="!selectedInvoice" class="card" style="display:none;">
            <div class="card-body" style="text-align:center;padding:40px 20px;">
                <i class="fas fa-file-invoice-dollar" style="font-size:40px;color:var(--text-4);margin-bottom:12px;display:block;"></i>
                <div style="font-size:13px;color:var(--text-3);font-weight:500;">Pilih invoice untuk melihat detail</div>
                <div style="font-size:11px;color:var(--text-4);margin-top:4px;">Preview tagihan akan muncul di sini</div>
            </div>
        </div>

        {{-- Invoice Detail Card --}}
        <div x-show="selectedInvoice" x-transition style="display:none;">
            <div class="card" style="border-color:rgba(99,102,241,0.3);">
                <div class="card-header" style="background:rgba(99,102,241,0.05);">
                    <div>
                        <div class="card-title" style="color:var(--indigo);">
                            <i class="fas fa-file-invoice-dollar" style="margin-right:6px;"></i>
                            <span x-text="selectedInvoice?.no_invoice ?? ''"></span>
                        </div>
                        <div class="card-subtitle" x-text="'Periode: ' + (selectedInvoice?.periode ?? '')"></div>
                    </div>
                    {{-- Status Badge --}}
                    <span x-show="selectedInvoice?.status === 'unpaid'"  class="badge badge-unpaid">Belum Bayar</span>
                    <span x-show="selectedInvoice?.status === 'partial'" class="badge badge-partial">Sebagian</span>
                    <span x-show="selectedInvoice?.status === 'paid'"    class="badge badge-paid">Lunas</span>
                </div>
                <div class="card-body">
                    {{-- Pelanggan Info --}}
                    <div style="background:var(--bg-elevated);border-radius:var(--radius);padding:12px;margin-bottom:14px;">
                        <div style="font-size:10px;color:var(--text-4);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px;">Pelanggan</div>
                        <div style="font-weight:600;color:var(--text-1);" x-text="selectedInvoice?.pelanggan_nama ?? '—'"></div>
                        <div class="mono-mute" style="margin-top:2px;" x-text="selectedInvoice?.username_pppoe ?? ''"></div>
                    </div>

                    {{-- Invoice Detail Rows --}}
                    <div class="info-row">
                        <span class="key">No. Invoice</span>
                        <span class="val" x-text="selectedInvoice?.no_invoice ?? '—'"></span>
                    </div>
                    <div class="info-row">
                        <span class="key">Periode</span>
                        <span class="val" x-text="selectedInvoice?.periode ?? '—'"></span>
                    </div>
                    <div class="info-row">
                        <span class="key">Total Tagihan</span>
                        <span class="val" style="color:var(--text-1);font-size:14px;font-weight:700;"
                              x-text="selectedInvoice ? 'Rp ' + Number(selectedInvoice.nominal).toLocaleString('id-ID') : '—'"></span>
                    </div>
                    <div class="info-row">
                        <span class="key">Jatuh Tempo</span>
                        <span class="val"
                              :style="selectedInvoice?.overdue ? 'color:var(--red);' : ''"
                              x-text="selectedInvoice?.tgl_jatuh_tempo_fmt ?? '—'"></span>
                    </div>
                    <div class="info-row" x-show="selectedInvoice?.keterangan">
                        <span class="key">Keterangan</span>
                        <span class="val" x-text="selectedInvoice?.keterangan ?? ''"></span>
                    </div>

                    {{-- Overdue warning --}}
                    <div x-show="selectedInvoice?.overdue"
                         x-transition
                         style="display:none;background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);border-radius:var(--radius);padding:10px 12px;margin-top:12px;">
                        <div style="color:#fca5a5;font-size:12px;font-weight:600;">
                            <i class="fas fa-triangle-exclamation" style="margin-right:4px;"></i>
                            Invoice ini sudah melewati jatuh tempo!
                        </div>
                    </div>

                    {{-- Auto-fill Nominal button --}}
                    <div style="margin-top:14px;">
                        <button type="button"
                                class="btn btn-ghost w-full"
                                style="justify-content:center;"
                                @click="
                                    if(selectedInvoice) {
                                        document.getElementById('inputNominal').value = selectedInvoice.nominal;
                                    }
                                ">
                            <i class="fas fa-wand-magic-sparkles"></i>
                            Isi Nominal Otomatis
                        </button>
                    </div>
                </div>
            </div>

            {{-- Metode Preview --}}
            <div class="card" style="margin-top:12px;">
                <div class="card-body" style="padding:14px;">
                    <div style="font-size:11px;color:var(--text-4);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Metode Terpilih</div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div style="width:32px;height:32px;border-radius:var(--radius-sm);background:var(--indigo-dim);display:flex;align-items:center;justify-content:center;">
                            <i class="fas" :class="isTransfer ? 'fa-building-columns' : 'fa-money-bill-wave'" style="color:var(--indigo);"></i>
                        </div>
                        <div>
                            <div style="font-weight:600;color:var(--text-1);font-size:13px;" x-text="metodeLabelMap[metode] || '—'"></div>
                            <div class="mono-mute" x-text="isTransferLain && $refs.namaBank ? $refs.namaBank.value : ''"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</div>{{-- end x-data --}}

@push('scripts')
<script>
    // If old invoice_id set from validation error, trigger Alpine event
    document.addEventListener('alpine:initialized', () => {
        const sel = document.querySelector('select[name="invoice_id"]');
        if (sel && sel.value) {
            sel.dispatchEvent(new Event('change'));
        }
    });
</script>
@endpush

@endsection
