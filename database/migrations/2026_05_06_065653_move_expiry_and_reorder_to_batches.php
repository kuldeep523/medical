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
        // Safe check for reorder_point on medicine_batches
        if (!Schema::hasColumn('medicine_batches', 'reorder_point')) {
            Schema::table('medicine_batches', function (Blueprint $table) {
                $table->integer('reorder_point')->default(10);
            });
        }

        // Safe drop for reorder_point on medicines (just in case)
        if (Schema::hasColumn('medicines', 'reorder_point')) {
            Schema::table('medicines', function (Blueprint $table) {
                $table->dropColumn('reorder_point');
            });
        }

        // Safe drop for expiry_date on medicines (just in case)
        if (Schema::hasColumn('medicines', 'expiry_date')) {
            Schema::table('medicines', function (Blueprint $table) {
                $table->dropColumn('expiry_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medicine_batches', function (Blueprint $table) {
            $table->dropColumn('reorder_point');
        });

        Schema::table('medicines', function (Blueprint $table) {
            $table->date('expiry_date')->nullable();
            $table->integer('reorder_point')->default(10);
        });
    }
};
