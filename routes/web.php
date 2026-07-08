<?php

use App\Http\Controllers\AdminConfigController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\BorrowingController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IncidentReportController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductUnitController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportGeneratorController;
use App\Http\Controllers\ReturnController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ============================================================
    // MASTER DATA — Staff & Admin
    // ============================================================
    Route::middleware('role:admin,staff')->group(function () {
        Route::get('/operasional', function () {
            return redirect()->route('dashboard');
        })->name('operasional.dashboard');

        // Categories resource (CRUD Master Data Kategori)
        Route::resource('categories', CategoryController::class);

        // Nested routes for product units
        Route::prefix('products/{product}/units')->group(function () {
            Route::get('/create', [ProductUnitController::class, 'create'])->name('units.create');
            Route::post('/', [ProductUnitController::class, 'store'])->name('units.store');
            Route::get('/{unit}', [ProductUnitController::class, 'show'])->name('units.show');
            Route::get('/{unit}/edit', [ProductUnitController::class, 'edit'])->name('units.edit');
            Route::put('/{unit}', [ProductUnitController::class, 'update'])->name('units.update');
        });

        Route::resource('products', ProductController::class)->except(['index', 'show']);

        // Label aset
        Route::get('/label/pilih', [LabelController::class, 'pilih'])->name('labels.pilih');
        Route::post('/label/cetak', [LabelController::class, 'cetak'])->name('labels.cetak');

        // Pengembalian
        Route::get('/pengembalian/cari', [ReturnController::class, 'search'])->name('returns.search');
        Route::get('/pengembalian/proses', [ReturnController::class, 'create'])->name('returns.create');
        Route::post('/pengembalian/proses', [ReturnController::class, 'store'])->name('returns.store');

        // Peminjaman kelola (Staff panel)
        Route::get('/peminjaman', [BorrowingController::class, 'index'])->name('borrowings.index');
        Route::get('/peminjaman/{id}/detail', [BorrowingController::class, 'show'])->name('borrowings.show');
        Route::post('/peminjaman/{id}/approve', [BorrowingController::class, 'approve'])->name('borrowings.approve');
        Route::post('/peminjaman/{id}/reject', [BorrowingController::class, 'reject'])->name('borrowings.reject');
        Route::get('/peminjaman/{id}/serah-terima', [BorrowingController::class, 'handover'])->name('borrowings.handover');
        Route::post('/peminjaman/{id}/serah-terima', [BorrowingController::class, 'confirmHandover'])->name('borrowings.confirmHandover');
    });

    // Products: semua role bisa lihat
    Route::middleware('role:admin,staff,manager,karyawan')->group(function () {
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
    });

    // ============================================================
    // PEMINJAMAN — Karyawan
    // ============================================================
    Route::middleware('role:admin,staff,karyawan')->group(function () {
        Route::get('/peminjaman/saya', [BorrowingController::class, 'my'])->name('borrowings.my');
        Route::get('/peminjaman/buat', [BorrowingController::class, 'create'])->name('borrowings.create');
        Route::post('/peminjaman/buat', [BorrowingController::class, 'store'])->name('borrowings.store');
        Route::post('/peminjaman/{id}/batal', [BorrowingController::class, 'cancel'])->name('borrowings.cancel');
        Route::post('/peminjaman-detail/{detailId}/perpanjang', [BorrowingController::class, 'extend'])->name('borrowings.extend');
        Route::get('/produk/{product}/ketersediaan', [AvailabilityController::class, 'calendar'])->name('availability.calendar');
    });

    // ============================================================
    // INSIDEN
    // ============================================================
    Route::middleware('role:karyawan')->group(function () {
        Route::get('/insiden/lapor', [IncidentReportController::class, 'create'])->name('incidents.create');
        Route::post('/insiden/lapor', [IncidentReportController::class, 'store'])->name('incidents.store');
    });
    Route::middleware('role:admin,staff,manager')->group(function () {
        Route::get('/insiden', [IncidentReportController::class, 'index'])->name('incidents.index');
    });
    Route::middleware('role:admin,staff,manager,karyawan')->group(function () {
        Route::get('/insiden/{id}', [IncidentReportController::class, 'show'])->name('incidents.show');
    });
    Route::middleware('role:admin,staff')->group(function () {
        Route::post('/insiden/{id}/verify', [IncidentReportController::class, 'verify'])->name('incidents.verify');
    });
    Route::middleware('role:admin')->group(function () {
        Route::post('/insiden/{id}/finalize', [IncidentReportController::class, 'finalize'])->name('incidents.finalize');
    });

    // ============================================================
    // LAPORAN — Admin, Staff, Manager
    // ============================================================
    Route::middleware('role:admin,staff,manager')->group(function () {
        Route::get('/laporan', [ReportGeneratorController::class, 'index'])->name('reports.index');
        Route::post('/laporan/generate', [ReportGeneratorController::class, 'generateReport'])->name('reports.generate');
        Route::get('/laporan/{id}', [ReportGeneratorController::class, 'show'])->name('reports.show');
        Route::get('/laporan/{id}/download', [ReportGeneratorController::class, 'download'])->name('reports.download');
    });

    // ============================================================
    // DASHBOARD MANAGER
    // ============================================================
    Route::middleware('role:admin,manager')->group(function () {
        Route::get('/dashboard/manager', [DashboardController::class, 'manager'])->name('dashboard.manager');
    });

    // ============================================================
    // ADMIN PANEL — Admin only
    // ============================================================
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/', fn () => redirect()->route('dashboard'))->name('admin.dashboard');
        Route::get('/users', [AdminConfigController::class, 'index'])->name('admin.users.index');
        Route::get('/users/{id}', [AdminConfigController::class, 'show'])->name('admin.users.show');
        Route::get('/hari-libur', [AdminConfigController::class, 'holidaysIndex'])->name('admin.holidays.index');
        Route::post('/hari-libur', [AdminConfigController::class, 'storeHoliday'])->name('admin.holidays.store');
        Route::delete('/hari-libur/{id}', [AdminConfigController::class, 'destroyHoliday'])->name('admin.holidays.destroy');
        Route::get('/token-api', [AdminConfigController::class, 'tokensIndex'])->name('admin.tokens.index');

        Route::post('/users/{id}/role', [AdminConfigController::class, 'updateUserRole'])->name('admin.users.update');
        Route::post('/hari-libur/sync', [AdminConfigController::class, 'syncHolidays'])->name('admin.holidays.sync');
        Route::post('/token-api/generate', [AdminConfigController::class, 'generateToken'])->name('admin.tokens.generate');
        Route::delete('/token-api/{id}', [AdminConfigController::class, 'revokeToken'])->name('admin.tokens.revoke');
    });

    // Riwayat peminjaman (semua role)
    Route::get('/riwayat-peminjaman', function () {
        return redirect()->route('borrowings.my');
    })->name('riwayat.peminjaman');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Notifikasi
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifikasi/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifikasi/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');
});

require __DIR__.'/auth.php';

// Public QR endpoint (no auth)
Route::get('/qr/{token}', [ProductUnitController::class, 'showByQr'])->name('qr.show');
