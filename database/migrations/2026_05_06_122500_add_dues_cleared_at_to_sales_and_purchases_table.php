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
        Schema::table('sales', function (Blueprint $table) {
            $table->timestamp('dues_cleared_at')->nullable();
        });
        Schema::table('purchases', function (Blueprint $table) {
            $table->timestamp('dues_cleared_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('dues_cleared_at');
        });
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('dues_cleared_at');
        });
    }
};
