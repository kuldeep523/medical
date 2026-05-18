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
            $table->decimal('purchase_price', 10, 2)->nullable()->after('quantity');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade')->after('purchase_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medicine_batches', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['purchase_price', 'user_id']);
        });
    }
};
