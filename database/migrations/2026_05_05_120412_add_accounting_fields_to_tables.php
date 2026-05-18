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
            $table->string('vendor_bill_path')->nullable();
            $table->string('vendor_name')->nullable();
            $table->decimal('amount_paid_to_vendor', 10, 2)->default(0);
            $table->string('return_status')->default('none');
        });

        Schema::table('medicines', function (Blueprint $table) {
            $table->integer('reorder_point')->default(10);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('payment_method')->default('Cash');
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->string('order_type')->default('Walk-in');
            $table->string('dispatch_status')->nullable();
            $table->string('bill_tag')->nullable();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained('stores')->cascadeOnDelete();
            $table->date('expense_date');
            $table->string('category');
            $table->decimal('amount', 10, 2);
            $table->text('description')->nullable();
            $table->string('payment_method')->default('Cash');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medicine_batches', function (Blueprint $table) {
            $table->dropColumn(['vendor_bill_path', 'vendor_name', 'amount_paid_to_vendor', 'return_status']);
        });

        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn('reorder_point');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['customer_name', 'customer_phone', 'payment_method', 'amount_paid', 'order_type', 'dispatch_status', 'bill_tag']);
        });

        Schema::dropIfExists('expenses');
    }
};
