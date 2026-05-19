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
            $table->string('patient_id')->nullable();
            $table->string('patient_name')->nullable();
            $table->string('patient_address')->nullable();
            $table->string('patient_reg_no')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['patient_id', 'patient_name', 'patient_address', 'patient_reg_no']);
        });
    }
};
