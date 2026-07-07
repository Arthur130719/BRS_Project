<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Invoice {{ $invoice->no_invoice }}</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Arial, sans-serif; font-size: 12px; color: #1e293b; background: white; padding: 24px; }
  .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 32px; }
  .company-name { font-size: 22px; font-weight: 700; color: #1e293b; }
  .company-sub { font-size: 11px; color: #64748b; margin-top: 2px; }
  .invoice-badge { background: #f1f5f9; border: 2px solid #6366f1; color: #4f46e5; padding: 8px 16px; border-radius: 8px; text-align: center; }
  .invoice-badge .no { font-size: 14px; font-weight: 700; letter-spacing: 1px; }
  .invoice-badge .label { font-size: 10px; color: #64748b; }
  .divider { border: none; border-top: 1px solid #e2e8f0; margin: 20px 0; }
  .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px; }
  .info-section .section-title { font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
  .info-row { display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px solid #f1f5f9; font-size: 12px; }
  .info-row .key { color: #64748b; }
  .info-row .val { font-weight: 500; color: #1e293b; }
  .amount-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; text-align: center; margin: 20px 0; }
  .amount-label { font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: 1px; }
  .amount-value { font-size: 28px; font-weight: 700; color: #4f46e5; margin-top: 4px; }
  .status-paid { background: #d1fae5; color: #065f46; padding: 4px 12px; border-radius: 100px; font-size: 11px; font-weight: 600; display: inline-block; }
  .status-unpaid { background: #fee2e2; color: #991b1b; padding: 4px 12px; border-radius: 100px; font-size: 11px; font-weight: 600; display: inline-block; }
  .status-partial { background: #fef3c7; color: #92400e; padding: 4px 12px; border-radius: 100px; font-size: 11px; font-weight: 600; display: inline-block; }
  .footer { margin-top: 32px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 16px; }
  .bank-info { background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 14px; margin-top: 16px; }
  .bank-info .title { font-size: 11px; font-weight: 700; color: #0369a1; margin-bottom: 8px; }
</style>
</head>
<body>
  <div class="header">
    <div>
      <div class="company-name">NetCORE</div>
      <div class="company-sub">ISP Management System</div>
      <div class="company-sub" style="margin-top:4px;">Bandung, Jawa Barat</div>
    </div>
    <div class="invoice-badge">
      <div class="label">NO. INVOICE</div>
      <div class="no">{{ $invoice->no_invoice }}</div>
    </div>
  </div>

  <hr class="divider">

  <div class="info-grid">
    <div class="info-section">
      <div class="section-title">Data Pelanggan</div>
      <div class="info-row"><span class="key">Nama</span><span class="val">{{ $invoice->pelanggan->nama }}</span></div>
      <div class="info-row"><span class="key">Username PPPoE</span><span class="val">{{ $invoice->pelanggan->username_pppoe }}</span></div>
      <div class="info-row"><span class="key">Paket</span><span class="val">{{ $invoice->pelanggan->paket?->nama ?? '-' }}</span></div>
      <div class="info-row"><span class="key">Telepon</span><span class="val">{{ $invoice->pelanggan->phone ?? '-' }}</span></div>
    </div>
    <div class="info-section">
      <div class="section-title">Info Invoice</div>
      <div class="info-row"><span class="key">Periode</span><span class="val">{{ $invoice->periode }}</span></div>
      <div class="info-row"><span class="key">Tgl Jatuh Tempo</span><span class="val">{{ $invoice->tgl_jatuh_tempo->format('d F Y') }}</span></div>
      <div class="info-row">
        <span class="key">Status</span>
        <span class="val">
          @if($invoice->status === 'paid') <span class="status-paid">LUNAS</span>
          @elseif($invoice->status === 'unpaid') <span class="status-unpaid">BELUM BAYAR</span>
          @else <span class="status-partial">PARSIAL</span>
          @endif
        </span>
      </div>
      @if($invoice->tgl_bayar)
      <div class="info-row"><span class="key">Tgl Bayar</span><span class="val">{{ $invoice->tgl_bayar->format('d F Y') }}</span></div>
      @endif
    </div>
  </div>

  <div class="amount-box">
    <div class="amount-label">Total Tagihan</div>
    <div class="amount-value">Rp {{ number_format($invoice->nominal, 0, ',', '.') }}</div>
  </div>

  @if($invoice->keterangan)
  <div style="font-size:12px; color:#64748b; margin-top:8px;">Keterangan: {{ $invoice->keterangan }}</div>
  @endif

  @if($invoice->status === 'unpaid')
  <div class="bank-info">
    <div class="title">ℹ️ INFORMASI PEMBAYARAN</div>
    <div style="font-size:11px; color:#0c4a6e;">
      Silakan lakukan transfer ke rekening berikut, kemudian konfirmasi ke kantor kami:<br>
      <strong>BCA:</strong> 1234567890 a/n PT NetCORE Indonesia<br>
      <strong>BRI:</strong> 0987654321 a/n PT NetCORE Indonesia<br>
      Sertakan nomor invoice: <strong>{{ $invoice->no_invoice }}</strong>
    </div>
  </div>
  @endif

  <div class="footer">
    <p>Dokumen ini digenerate secara otomatis oleh sistem NetCORE pada {{ now()->format('d F Y, H:i') }}</p>
    <p>Untuk pertanyaan hubungi tim support NetCORE</p>
  </div>
</body>
</html>
