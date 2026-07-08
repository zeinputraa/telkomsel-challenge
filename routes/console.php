<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ─── Penjadwalan Otomatis Peminjaman & Notifikasi ─────────────────────────────

// 1. Lepas unit yang sudah di-booking tapi tidak diambil peminjam (no-show 1 hari kerja)
Schedule::command('borrowings:release-no-show')->daily();

// 2. Batalkan pengajuan yang tidak diproses Staff sampai lewat tanggal rencana (SLA expired)
Schedule::command('borrowings:auto-cancel')->daily();

// 3. Cek stok produk di bawah minimum
Schedule::command('notifications:check-stock')->daily();

// 4. Kirim reminder jatuh tempo besok
Schedule::command('notifications:due-soon-reminders')->daily();

// 5. Kirim notifikasi keterlambatan unit
Schedule::command('notifications:overdue')->daily();
