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
        Schema::table('medicine_batches', function (Blueprint $table) {
            $table->decimal('sales_price', 10, 2)->after('purchase_price')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medicine_batches', function (Blueprint $table) {
            $table->dropColumn('sales_price');
        });
    }
};
