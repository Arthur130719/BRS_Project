<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Pembayaran;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        $query = Pembayaran::with(['invoice.pelanggan', 'user']);

        // Filter by Search
        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('invoice', function ($q) use ($s) {
                $q->where('no_invoice', 'like', "%$s%")
                  ->orWhereHas('pelanggan', fn($q2) => $q2->where('nama', 'like', "%$s%"));
            });
        }

        // Filter by Metode
        if ($request->filled('metode')) {
            $query->where('metode', $request->metode);
        }

        // Filter by Bulan & Tahun (default to current month/year if not specified and not searching)
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));
        
        // Apply month/year filter
        if ($bulan) {
            $query->whereMonth('tgl_bayar', $bulan);
        }
        if ($tahun) {
            $query->whereYear('tgl_bayar', $tahun);
        }

        // ── Summary totals (Bulan Ini / Filtered) ──
        $summaryQuery = clone $query;
        // Remove eager loading for summary queries to optimize
        $summaryQuery->setEagerLoads([]); 
        
        $totalCashBulanIni        = (clone $summaryQuery)->where('metode', 'cash')->sum('nominal');
        $totalTransferAllBulanIni = (clone $summaryQuery)->where('metode', '!=', 'cash')->sum('nominal');
        $grandTotalBulanIni       = (clone $summaryQuery)->sum('nominal');

        // Total per rekening bank (Bulan Ini)
        $totalPerBank = (clone $summaryQuery)
            ->where('metode', '!=', 'cash')
            ->selectRaw('metode, nama_bank, SUM(nominal) as total')
            ->groupBy('metode', 'nama_bank')
            ->orderBy('metode')
            ->get();

        // ── Grand Total Seluruh Waktu (Unfiltered) ──
        $grandTotalKeseluruhan = Pembayaran::sum('nominal');

        $pembayarans = $query->latest('tgl_bayar')->latest('id')->paginate(20)->withQueryString();

        return view('pembayaran.index', compact(
            'pembayarans',
            'totalCashBulanIni',
            'totalTransferAllBulanIni',
            'grandTotalBulanIni',
            'grandTotalKeseluruhan',
            'totalPerBank',
            'bulan',
            'tahun'
        ));
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

    public function store(Request $request, \App\Services\MikrotikService $mikrotikService)
    {
        $validated = $request->validate([
            'invoice_id'  => 'required|exists:invoices,id',
            'nominal'     => 'required|numeric|min:1000',
            'metode'      => 'required|string|max:100',
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
                
                if ($pelanggan->nas) {
                    $profileName = $pelanggan->paket->mikrotik_profile ?: $pelanggan->paket->nama;
                    $mikrotikService->changePppoeProfile($pelanggan->nas, $pelanggan->username_pppoe, $profileName);
                }
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
