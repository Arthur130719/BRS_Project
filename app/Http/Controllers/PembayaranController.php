<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Pembayaran;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        $pembayarans = Pembayaran::with(['invoice.pelanggan', 'user'])
            ->latest()
            ->paginate(20);
        return view('pembayaran.index', compact('pembayarans'));
    }

    public function create(Request $request)
    {
        $invoices = Invoice::with('pelanggan')
            ->whereIn('status', ['unpaid', 'partial'])
            ->orderBy('tgl_jatuh_tempo')
            ->get();
        $selectedInvoice = $request->filled('invoice_id')
            ? Invoice::with('pelanggan.paket')->find($request->invoice_id)
            : null;

        return view('pembayaran.create', compact('invoices', 'selectedInvoice'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_id'  => 'required|exists:invoices,id',
            'nominal'     => 'required|numeric|min:1000',
            'metode'      => 'required|in:cash,transfer_bca,transfer_bri,transfer_mandiri,transfer_bni,transfer_lain',
            'nama_bank'   => 'nullable|max:100',
            'tgl_bayar'   => 'required|date',
            'keterangan'  => 'nullable|max:255',
            'bukti_transfer' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        $validated['user_id'] = auth()->id();

        // Handle file upload
        if ($request->hasFile('bukti_transfer')) {
            $validated['bukti_transfer'] = $request->file('bukti_transfer')
                ->store('bukti_transfer', 'public');
        }

        $pembayaran = Pembayaran::create($validated);

        // Update invoice status
        $invoice        = Invoice::findOrFail($validated['invoice_id']);
        $totalBayar     = $invoice->pembayarans()->sum('nominal');
        if ($totalBayar >= $invoice->nominal) {
            $invoice->update(['status' => 'paid', 'tgl_bayar' => $validated['tgl_bayar']]);

            // Auto-aktifkan pelanggan jika perlu
            $pelanggan = $invoice->pelanggan;
            if ($pelanggan->isSuspended() && $pelanggan->invoices()->where('status', 'unpaid')->count() === 0) {
                $pelanggan->update(['status' => 'active', 'isolir_by' => null, 'isolir_at' => null]);
                \App\Models\IsolirLog::create([
                    'pelanggan_id' => $pelanggan->id,
                    'invoice_id'   => $invoice->id,
                    'aksi'         => 'aktifkan',
                    'metode'       => 'manual',
                    'user_id'      => auth()->id(),
                    'alasan'       => 'Diaktifkan setelah pembayaran lunas via ' . $pembayaran->metode_label,
                ]);
            }
        } else {
            $invoice->update(['status' => 'partial']);
        }

        return redirect()->route('pembayaran.index')->with('success', 'Pembayaran berhasil dicatat.');
    }

    public function show(Pembayaran $pembayaran)
    {
        $pembayaran->load(['invoice.pelanggan.paket', 'user']);
        return view('pembayaran.show', compact('pembayaran'));
    }
}
