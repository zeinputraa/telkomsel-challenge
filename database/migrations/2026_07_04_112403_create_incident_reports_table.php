<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('incident_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrowing_detail_id')->constrained('borrowing_details');
            $table->foreignId('product_unit_id')->constrained('product_units');
            $table->foreignId('reported_by')->constrained('users')->comment('karyawan pelapor');
            $table->enum('jenis', ['rusak_ringan', 'rusak_berat', 'hilang']);
            $table->text('kronologi');
            $table->string('foto_bukti')->nullable();
            $table->enum('status', [
                'menunggu_verifikasi_staff', 'terverifikasi_staff', 'menunggu_finalisasi_admin',
                'dibatalkan_ditemukan', 'difinalisasi_admin',
            ])->default('menunggu_verifikasi_staff');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->date('batas_investigasi')->nullable()->comment('khusus jenis=hilang');
            $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete()
                ->comment('Admin only');
            $table->dateTime('finalized_at')->nullable();
            $table->enum('status_ganti_rugi', ['belum_diselesaikan', 'proses_ganti_rugi', 'selesai'])
                ->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_reports');
    }
};
