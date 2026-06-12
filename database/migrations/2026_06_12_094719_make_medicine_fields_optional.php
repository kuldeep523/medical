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
        Schema::table('medicines', function (Blueprint $table) {
            $table->string('rx_salt')->nullable()->change();
            $table->string('power_mg')->nullable()->change();
            $table->string('brand_name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->string('rx_salt')->nullable(false)->change();
            $table->string('power_mg')->nullable(false)->change();
            $table->string('brand_name')->nullable(false)->change();
        });
    }
};
