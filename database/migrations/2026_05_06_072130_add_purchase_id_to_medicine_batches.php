<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicine_batches', function (Blueprint $table) {
            if (!Schema::hasColumn('medicine_batches', 'purchase_id')) {
                $table->foreignId('purchase_id')->nullable()->constrained('purchases')->onDelete('set null');
            } else {
                // If column exists but maybe constraint doesn't
                try {
                    $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('set null');
                } catch (\Exception $e) {
                    // Ignore if constraint already exists
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('medicine_batches', function (Blueprint $table) {
            $table->dropForeign(['purchase_id']);
            $table->dropColumn('purchase_id');
        });
    }
};
