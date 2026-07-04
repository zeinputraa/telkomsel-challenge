<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Khusus Admin
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        // route kelola user, dsb - diisi di fase berikutnya
        Route::get('/', function () {
            return 'admin dashboard placeholder';
        })->name('admin.dashboard');
    });

    // Staff & Admin (operasional inventaris)
    Route::middleware('role:admin,staff')->group(function () {
        // route master data barang, approval peminjaman - diisi di fase berikutnya
        Route::get('/operasional', function () {
            return 'operasional placeholder';
        })->name('operasional.dashboard');
    });

    // Semua role yang login boleh akses (Karyawan termasuk)
    Route::get('/riwayat-peminjaman', function () {
        return 'placeholder';
    })->name('riwayat.peminjaman');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
