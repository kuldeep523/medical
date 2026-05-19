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
        if (!Schema::hasColumn('medicines', 'reorder_point')) {
            Schema::table('medicines', function (Blueprint $table) {
                $table->integer('reorder_point')->default(10);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('medicines', 'reorder_point')) {
            Schema::table('medicines', function (Blueprint $table) {
                $table->dropColumn('reorder_point');
            });
        }
    }
};
