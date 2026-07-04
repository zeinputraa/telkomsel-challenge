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
        Schema::create('borrowings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->comment('peminjam');
            $table->dateTime('tanggal_pengajuan');
            $table->date('tanggal_pinjam_rencana');
            $table->date('tanggal_kembali_rencana');
            $table->enum('status', [
                'diajukan', 'disetujui', 'ditolak', 'berjalan', 'selesai',
                'dibatalkan_user', 'dibatalkan_otomatis',
            ])->default('diajukan');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->boolean('fifo_override')->default(false);
            $table->text('alasan_override')->nullable();
            $table->text('alasan_penolakan')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index(['tanggal_pinjam_rencana', 'tanggal_kembali_rencana']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrowings');
    }
};
