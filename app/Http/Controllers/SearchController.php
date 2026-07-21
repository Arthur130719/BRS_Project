<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pelanggan;
use App\Models\Invoice;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q');
        
        $pelanggan = collect();
        $invoices = collect();

        if (!empty($query)) {
            // Search Pelanggan by name, username, IP, phone, or address
            $pelanggan = Pelanggan::where('nama', 'like', "%{$query}%")
                ->orWhere('username_pppoe', 'like', "%{$query}%")
                ->orWhere('ip_address', 'like', "%{$query}%")
                ->orWhere('phone', 'like', "%{$query}%")
                ->orWhere('alamat', 'like', "%{$query}%")
                ->take(10)
                ->get();
                
            // Search Invoices by invoice number
            $invoices = Invoice::with('pelanggan')
                ->where('no_invoice', 'like', "%{$query}%")
                ->orWhereHas('pelanggan', function($q) use ($query) {
                    $q->where('nama', 'like', "%{$query}%")
                      ->orWhere('username_pppoe', 'like', "%{$query}%");
                })
                ->take(10)
                ->get();
        }

        return view('search.index', compact('query', 'pelanggan', 'invoices'));
    }

    public function live(Request $request)
    {
        $query = $request->input('q');
        
        if (empty($query) || strlen($query) < 2) {
            return response()->json(['pelanggan' => [], 'invoices' => []]);
        }

        // Limit fields and results for instant speed
        $pelanggan = Pelanggan::select('id', 'nama', 'username_pppoe', 'ip_address', 'status')
            ->where('nama', 'like', "%{$query}%")
            ->orWhere('username_pppoe', 'like', "%{$query}%")
            ->orWhere('ip_address', 'like', "%{$query}%")
            ->take(5)
            ->get();

        $invoices = Invoice::select('id', 'no_invoice', 'pelanggan_id', 'nominal', 'status')
            ->with(['pelanggan:id,nama'])
            ->where('no_invoice', 'like', "%{$query}%")
            ->take(5)
            ->get();

        return response()->json([
            'pelanggan' => $pelanggan,
            'invoices' => $invoices
        ]);
    }
}
