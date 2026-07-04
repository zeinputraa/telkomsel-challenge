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
        Schema::create('borrowing_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrowing_id')->constrained('borrowings');
            $table->foreignId('product_id')->constrained('products')->comment('produk yang diminta');
            $table->foreignId('product_unit_id')->nullable()->constrained('product_units')->nullOnDelete()
                ->comment('terisi saat approval (unit assignment)');
            $table->enum('status', [
                'diajukan', 'disetujui', 'ditolak', 'dipinjam', 'dikembalikan',
                'terlambat', 'bermasalah', 'selesai_bermasalah',
            ])->default('diajukan');
            $table->date('tanggal_kembali_rencana')->comment('bisa override individual saat extend');
            $table->dateTime('tanggal_pinjam_aktual')->nullable()->comment('diisi saat scan QR serah terima');
            $table->dateTime('tanggal_kembali_aktual')->nullable();
            $table->enum('kondisi_saat_pinjam', ['baik', 'rusak_ringan', 'rusak_berat'])->nullable();
            $table->enum('kondisi_saat_kembali', ['baik', 'rusak_ringan', 'rusak_berat'])->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('tanggal_kembali_rencana');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrowing_details');
    }
};
