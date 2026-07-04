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
        Schema::create('report_archives', function (Blueprint $table) {
            $table->id();
            $table->enum('jenis', ['bulanan', 'kuartalan', 'tahunan', 'custom']);
            $table->date('periode_mulai');
            $table->date('periode_selesai');
            $table->decimal('total_nilai_aset', 18, 2)->nullable();
            $table->decimal('total_kerugian', 18, 2)->nullable();
            $table->string('file_pdf_path')->nullable();
            $table->string('file_excel_path')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete()
                ->comment('null jika auto-generate terjadwal');
            $table->dateTime('generated_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_archives');
    }
};
