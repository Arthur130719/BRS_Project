<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DistributedQueryController extends Controller
{
    /**
     * Endpoint API untuk mendemonstrasikan sistem basis data terdistribusi.
     * Mengambil data pengaduan dari Cabang A dan Cabang B, 
     * lalu menggabungkannya secara terpusat.
     */
    public function getDistributedReport()
    {
        try {
            // Ambil data dari Cabang A
            $dataCabangA = DB::connection('mysql_cabang_a')
                ->table('pengaduan_cabang')
                ->get()
                ->map(function ($item) {
                    $item->sumber_database = 'netcore_cabang_a (Timur)';
                    return $item;
                });

            // Ambil data dari Cabang B
            $dataCabangB = DB::connection('mysql_cabang_b')
                ->table('pengaduan_cabang')
                ->get()
                ->map(function ($item) {
                    $item->sumber_database = 'netcore_cabang_b (Barat)';
                    return $item;
                });

            // Agregasi / Penggabungan Data di Pusat
            $gabunganData = $dataCabangA->merge($dataCabangB);

            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil ditarik dari Sistem Basis Data Terdistribusi (Multi-Database)',
                'arsitektur' => [
                    'pusat' => 'netcore (Central DB)',
                    'nodes' => ['netcore_cabang_a', 'netcore_cabang_b']
                ],
                'total_pengaduan_terpusat' => $gabunganData->count(),
                'data' => $gabunganData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal terhubung ke node cabang database: ' . $e->getMessage()
            ], 500);
        }
    }
}
