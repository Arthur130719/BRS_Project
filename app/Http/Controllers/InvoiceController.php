<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Notifikasi;
use App\Models\Paket;
use App\Models\Pelanggan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with('pelanggan');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('no_invoice', 'like', "%$s%")
                ->orWhereHas('pelanggan', fn($q2) => $q2->where('nama', 'like', "%$s%"))
            );
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('periode')) {
            $query->where('periode', 'like', '%' . $request->periode . '%');
        }

        $invoices = $query->latest()->paginate(20)->withQueryString();
        $pelanggans = Pelanggan::with('paket')->orderBy('nama')->get();

        return view('invoice.index', compact('invoices', 'pelanggans'));
    }

    public function create()
    {
        $pelanggans = Pelanggan::with('paket')->orderBy('nama')->get();
        return view('invoice.create', compact('pelanggans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pelanggan_id'    => 'required|exists:pelanggans,id',
            'periode'         => 'required|max:50',
            'nominal'         => 'required|numeric|min:0',
            'tgl_jatuh_tempo' => 'required|date',
            'keterangan'      => 'nullable|max:255',
        ]);

        $validated['no_invoice'] = Invoice::generateNoInvoice();
        $validated['status']     = 'unpaid';

        $invoice = Invoice::create($validated);

        Notifikasi::create([
            'type'      => 'info',
            'title'     => 'Invoice Baru: ' . $invoice->no_invoice,
            'deskripsi' => $invoice->pelanggan->nama . ' — ' . $invoice->periode . ' — ' . $invoice->nominal_format,
        ]);

        return redirect()->route('invoice.index')->with('success', 'Invoice ' . $invoice->no_invoice . ' berhasil dibuat.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['pelanggan.paket', 'pembayarans.user']);
        return view('invoice.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $pelanggans = Pelanggan::with('paket')->orderBy('nama')->get();
        return view('invoice.edit', compact('invoice', 'pelanggans'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'pelanggan_id'    => 'required|exists:pelanggans,id',
            'periode'         => 'required|max:50',
            'nominal'         => 'required|numeric|min:0',
            'tgl_jatuh_tempo' => 'required|date',
            'status'          => 'required|in:unpaid,paid,partial',
            'keterangan'      => 'nullable|max:255',
        ]);

        $invoice->update($validated);
        return redirect()->route('invoice.index')->with('success', 'Invoice berhasil diperbarui.');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return redirect()->route('invoice.index')->with('success', 'Invoice berhasil dihapus.');
    }

    public function tandaiLunas(Request $request, int $id, \App\Services\MikrotikService $mikrotikService)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->update([
            'status'    => 'paid',
            'tgl_bayar' => today(),
        ]);

        $metode = $request->input('metode', 'cash');

        // Buat record pembayaran otomatis
        \App\Models\Pembayaran::create([
            'invoice_id' => $invoice->id,
            'nominal'    => $invoice->nominal,
            'metode'     => $metode,
            'tgl_bayar'  => today(),
            'keterangan' => 'Ditandai lunas cepat via menu Invoice',
            'user_id'    => auth()->id(),
        ]);

        // Periksa apakah pelanggan perlu diaktifkan kembali
        $pelanggan = $invoice->pelanggan;
        if ($pelanggan->isSuspended()) {
            $unpaidCount = $pelanggan->invoices()->where('status', 'unpaid')->count();
            if ($unpaidCount === 0) {
                $pelanggan->update([
                    'status'    => 'active',
                    'isolir_by' => null,
                    'isolir_at' => null,
                ]);
                \App\Models\IsolirLog::create([
                    'pelanggan_id' => $pelanggan->id,
                    'invoice_id'   => $invoice->id,
                    'aksi'         => 'aktifkan',
                    'metode'       => 'manual',
                    'user_id'      => auth()->id(),
                    'alasan'       => 'Diaktifkan otomatis setelah semua tagihan lunas',
                ]);
                
                if ($pelanggan->nas) {
                    $profileName = $pelanggan->paket->mikrotik_profile ?: $pelanggan->paket->nama;
                    $mikrotikService->changePppoeProfile($pelanggan->nas, $pelanggan->username_pppoe, $profileName);
                }
            }
        }

        return back()->with('success', 'Invoice ' . $invoice->no_invoice . ' ditandai lunas.');
    }

    public function exportPdf(int $id)
    {
        $invoice = Invoice::with(['pelanggan.paket'])->findOrFail($id);
        $pdf = Pdf::loadView('invoice.pdf', compact('invoice'));
        return $pdf->download('invoice-' . $invoice->no_invoice . '.pdf');
    }
}
