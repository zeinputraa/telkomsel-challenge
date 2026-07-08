<?php

use App\Enums\StatusUnit;
use App\Models\Holiday;
use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ============================================================
// API V1 ENDPOINTS — Untuk Integrasi Sistem / Pengujian
// ============================================================
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {

    // Get all items in inventory catalog (Real-time dynamic DB query)
    Route::get('/barang', function (Request $request) {
        // Cek kemampuan token (ability)
        if (! $request->user()->tokenCan('read') && ! $request->user()->tokenCan('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized token ability.',
            ], 403);
        }

        $products = Product::withCount(['units' => function ($q) {
            $q->where('status', StatusUnit::Tersedia->value);
        }])->get();

        $data = $products->map(function ($product) {
            return [
                'kode_produk' => $product->kode_produk,
                'nama_barang' => $product->nama_barang,
                'kategori' => $product->category->nama_kategori ?? 'Umum',
                'total_unit' => $product->units()->count(),
                'unit_tersedia' => $product->units_count,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    });

    // Check specific unit status
    Route::get('/unit/{kode}/status', function (Request $request, $kode) {
        if (! $request->user()->tokenCan('read') && ! $request->user()->tokenCan('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized token ability.',
            ], 403);
        }

        $unit = ProductUnit::with('product')
            ->where('kode_unit', strtoupper($kode))
            ->first();

        if (! $unit) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode unit tidak terdaftar.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'kode_unit' => $unit->kode_unit,
                'nama_barang' => $unit->product->nama_barang ?? 'N/A',
                'kondisi' => $unit->kondisi->value ?? $unit->kondisi,
                'status' => $unit->status->value ?? $unit->status,
                'lokasi' => $unit->lokasi_penyimpanan ?? 'N/A',
            ],
        ]);
    });

    // Get list of holidays for penalty SLA calculation
    Route::get('/hari-libur', function (Request $request) {
        if (! $request->user()->tokenCan('read') && ! $request->user()->tokenCan('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized token ability.',
            ], 403);
        }

        $holidays = Holiday::orderBy('tanggal', 'asc')->get();

        $data = $holidays->map(function ($holiday) {
            return [
                'nama' => $holiday->keterangan,
                'tanggal' => $holiday->tanggal->format('Y-m-d'),
                'tipe' => $holiday->jenis,
            ];
        });

        return response()->json([
            'status' => 'success',
            'year' => date('Y'),
            'data' => $data,
        ]);
    });
});
