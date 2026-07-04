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
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->string('kode_unit', 50)->unique();
            $table->string('qr_code')->unique()->comment('token/path QR untuk stiker');
            $table->enum('kondisi', ['baik', 'rusak_ringan', 'rusak_berat'])->default('baik');
            $table->enum('status', [
                'tersedia', 'dipinjam', 'maintenance', 'dilaporkan_hilang', 'hilang_permanen',
            ])->default('tersedia');
            $table->string('lokasi_penyimpanan', 150);
            $table->year('tahun_pengadaan');
            $table->decimal('harga_perolehan', 15, 2)->comment('untuk valuasi laporan tahunan');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_units');
    }
};
