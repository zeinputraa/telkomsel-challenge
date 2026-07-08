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
        Schema::table('borrowings', function (Blueprint $table) {
            $table->boolean('needs_manager_approval')->default(false);
            $table->boolean('manager_approved')->nullable();
            $table->foreignId('manager_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('manager_approved_at')->nullable();
            $table->text('manager_alasan_penolakan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            $table->dropForeign(['manager_approved_by']);
            $table->dropColumn([
                'needs_manager_approval',
                'manager_approved',
                'manager_approved_by',
                'manager_approved_at',
                'manager_alasan_penolakan',
            ]);
        });
    }
};
