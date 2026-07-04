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
        Schema::create('borrowing_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrowing_detail_id')->constrained('borrowing_details');
            $table->dateTime('tanggal_pengembalian');
            $table->foreignId('diterima_oleh')->constrained('users')->comment('Staff penerima');
            $table->enum('kondisi_barang', ['baik', 'rusak_ringan', 'rusak_berat']);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrowing_returns');
    }
};
