<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Invoice {{ $invoice->no_invoice }}</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #334155; line-height: 1.5; padding: 30px; }
  table { width: 100%; border-collapse: collapse; }
  .text-right { text-align: right; }
  .text-left { text-align: left; }
  .text-center { text-align: center; }
  .font-bold { font-weight: bold; }
  
  /* Header */
  .header-table { margin-bottom: 30px; border-bottom: 2px solid #e2e8f0; padding-bottom: 20px; }
  .company-name { font-size: 24px; font-weight: bold; color: #0f172a; letter-spacing: 1px; }
  .company-details { font-size: 11px; color: #64748b; margin-top: 5px; line-height: 1.6; }
  .invoice-title { font-size: 36px; font-weight: bold; color: #e2e8f0; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 5px; }
  .invoice-no { font-size: 16px; font-weight: bold; color: #4f46e5; }
  
  /* Meta Info */
  .meta-table { margin-bottom: 30px; }
  .meta-box { background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px solid #e2e8f0; }
  .meta-heading { font-size: 10px; font-weight: bold; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
  .meta-text { font-size: 13px; color: #1e293b; font-weight: 500; }
  .meta-sub { font-size: 11px; color: #64748b; margin-top: 3px; }
  
  /* Items Table */
  .items-table { margin-bottom: 30px; }
  .items-table th { background: #4f46e5; color: white; padding: 12px; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; border: none; }
  .items-table td { padding: 15px 12px; border-bottom: 1px solid #e2e8f0; color: #1e293b; }
  .items-table .item-desc { font-size: 13px; font-weight: bold; }
  .items-table .item-sub { font-size: 11px; color: #64748b; margin-top: 4px; }
  
  /* Totals */
  .total-table { width: 45%; float: right; margin-bottom: 40px; }
  .total-row td { padding: 10px 12px; border-bottom: 1px solid #f1f5f9; }
  .total-row.grand-total td { font-size: 16px; font-weight: bold; color: #4f46e5; border-bottom: none; border-top: 2px solid #e2e8f0; background: #f8fafc;}
  
  /* Status Badge */
  .status-badge { display: inline-block; padding: 6px 12px; border-radius: 4px; font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-top: 10px; }
  .status-paid { background: #d1fae5; color: #065f46; border: 1px solid #34d399; }
  .status-unpaid { background: #fee2e2; color: #991b1b; border: 1px solid #f87171; }
  .status-partial { background: #fef3c7; color: #92400e; border: 1px solid #fbbf24; }
  
  /* Notes & Footer */
  .notes { font-size: 11px; color: #64748b; line-height: 1.6; clear: both; margin-top: 50px;}
  .notes .title { font-weight: bold; color: #1e293b; margin-bottom: 5px; }
  .footer { margin-top: 40px; border-top: 1px solid #e2e8f0; padding-top: 15px; text-align: center; font-size: 10px; color: #94a3b8; }
</style>
</head>
<body>

  <!-- Header -->
  <table class="header-table">
    <tr>
      <td width="50%" valign="top">
        <img src="{{ public_path('img/logo.png') }}" alt="BRS Logo" style="height: 50px; margin-bottom: 15px;">
        <div class="company-name">PT. BINA RAJA SOLUSI</div>
        <div class="company-details">
          Jl. Raya Permata No.11, Saga, Balaraja<br>
          Kabupaten Tangerang, Banten 15610<br>
          Telp: 087761205991<br>
          Email: ptbinarajasolusi12345@gmail.com
        </div>
      </td>
      <td width="50%" valign="top" class="text-right">
        <div class="invoice-title">INVOICE</div>
        <div class="invoice-no">{{ $invoice->no_invoice }}</div>
        
        <div>
          @if($invoice->status === 'paid') <span class="status-badge status-paid">LUNAS</span>
          @elseif($invoice->status === 'unpaid') <span class="status-badge status-unpaid">BELUM LUNAS</span>
          @else <span class="status-badge status-partial">PARSIAL</span>
          @endif
        </div>
      </td>
    </tr>
  </table>

  <!-- Meta Info -->
  <table class="meta-table">
    <tr>
      <td width="48%" valign="top">
        <div class="meta-box">
          <div class="meta-heading">Tagihan Kepada:</div>
          <div class="meta-text">{{ $invoice->pelanggan->nama }}</div>
          <div class="meta-sub">PPPoE: {{ $invoice->pelanggan->username_pppoe }}</div>
          <div class="meta-sub">Telp: {{ $invoice->pelanggan->phone ?? '-' }}</div>
        </div>
      </td>
      <td width="4%"></td>
      <td width="48%" valign="top">
        <div class="meta-box">
          <div class="meta-heading">Detail Tagihan:</div>
          <table width="100%">
            <tr>
              <td class="meta-sub" width="45%">Tgl Terbit</td>
              <td class="meta-text text-right">{{ $invoice->created_at->format('d M Y') }}</td>
            </tr>
            <tr>
              <td class="meta-sub">Jatuh Tempo</td>
              <td class="meta-text text-right" style="color: #ef4444;">{{ $invoice->tgl_jatuh_tempo->format('d M Y') }}</td>
            </tr>
            @if($invoice->tgl_bayar)
            <tr>
              <td class="meta-sub">Tgl Bayar</td>
              <td class="meta-text text-right" style="color: #10b981;">{{ $invoice->tgl_bayar->format('d M Y') }}</td>
            </tr>
            @endif
          </table>
        </div>
      </td>
    </tr>
  </table>

  <!-- Items Table -->
  <table class="items-table">
    <thead>
      <tr>
        <th class="text-left" width="55%">Deskripsi Tagihan</th>
        <th class="text-center" width="15%">Periode</th>
        <th class="text-center" width="10%">Qty</th>
        <th class="text-right" width="20%">Jumlah</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td valign="top">
          <div class="item-desc">Layanan Internet: {{ $invoice->pelanggan->paket?->nama ?? 'Paket Internet' }}</div>
          @if($invoice->keterangan)
          <div class="item-sub">Keterangan: {{ $invoice->keterangan }}</div>
          @endif
        </td>
        <td valign="top" class="text-center">{{ $invoice->periode }}</td>
        <td valign="top" class="text-center">1</td>
        <td valign="top" class="text-right font-bold">Rp {{ number_format($invoice->nominal, 0, ',', '.') }}</td>
      </tr>
    </tbody>
  </table>

  <!-- Totals -->
  <table class="total-table">
    <tr class="total-row">
      <td class="text-left">Subtotal</td>
      <td class="text-right">Rp {{ number_format($invoice->nominal, 0, ',', '.') }}</td>
    </tr>
    @php
        $terbayar = $invoice->pembayarans()->sum('nominal');
        $sisa = $invoice->nominal - $terbayar;
    @endphp
    @if($terbayar > 0)
    <tr class="total-row">
      <td class="text-left">Telah Dibayar</td>
      <td class="text-right" style="color: #10b981;">- Rp {{ number_format($terbayar, 0, ',', '.') }}</td>
    </tr>
    @endif
    <tr class="total-row grand-total">
      <td class="text-left">Total Tagihan</td>
      <td class="text-right">Rp {{ number_format($sisa > 0 ? $sisa : 0, 0, ',', '.') }}</td>
    </tr>
  </table>

  <!-- Notes -->
  <div class="notes">
    @if($invoice->status !== 'paid')
    <div class="title">Informasi Pembayaran:</div>
    Harap melakukan pembayaran penuh sebelum tanggal jatuh tempo melalui transfer bank ke rekening berikut:<br>
    <ul style="margin-top: 8px; margin-left: 20px;">
      <li><strong>BCA</strong>: 6280939267 a.n AISYAH NURUL ISTIQOMAH</li>
      <li><strong>MANDIRI</strong>: 1760003390752 a.n BINA RAJA SOLUSI</li>
    </ul>
    <div style="margin-top:8px;">Jangan lupa menyertakan nomor invoice <strong>{{ $invoice->no_invoice }}</strong> pada keterangan transfer.</div>
    @else
    <div class="title" style="color:#065f46;">Terima kasih atas pembayaran Anda!</div>
    Invoice ini telah dinyatakan lunas dan menjadi tanda terima pembayaran yang sah.
    @endif
  </div>

  <div class="footer">
    Dokumen ini diterbitkan secara otomatis oleh sistem BRS pada {{ now()->format('d F Y, H:i') }}.<br>
    PT. BINA RAJA SOLUSI
  </div>

</body>
</html>
