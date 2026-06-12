<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add columns to medicine_batches
        Schema::table('medicine_batches', function (Blueprint $table) {
            $table->integer('units_per_strip')->default(1)->after('quantity');
            $table->string('location_section')->nullable()->after('reorder_point');
            $table->string('location_column')->nullable()->after('location_section');
        });

        // 2. Data Migration: Copy from medicines to medicine_batches
        DB::table('medicines')->orderBy('id')->chunk(100, function ($medicines) {
            foreach ($medicines as $medicine) {
                DB::table('medicine_batches')
                    ->where('medicine_id', $medicine->id)
                    ->update([
                        'units_per_strip'  => $medicine->units_per_strip ?? 1,
                        'location_section' => $medicine->location_section,
                        'location_column'  => $medicine->location_column,
                    ]);
            }
        });

        // 3. Drop columns from medicines
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn(['units_per_strip', 'location_section', 'location_column']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Add columns back to medicines
        Schema::table('medicines', function (Blueprint $table) {
            $table->integer('units_per_strip')->default(1);
            $table->string('location_section')->nullable();
            $table->string('location_column')->nullable();
        });

        // 2. Data Migration: Copy from medicine_batches to medicines
        // (This might lose varying batch data, but takes the first batch for a medicine)
        DB::table('medicine_batches')->orderBy('id')->chunk(100, function ($batches) {
            foreach ($batches as $batch) {
                DB::table('medicines')
                    ->where('id', $batch->medicine_id)
                    ->update([
                        'units_per_strip'  => $batch->units_per_strip ?? 1,
                        'location_section' => $batch->location_section,
                        'location_column'  => $batch->location_column,
                    ]);
            }
        });

        // 3. Drop columns from medicine_batches
        Schema::table('medicine_batches', function (Blueprint $table) {
            $table->dropColumn(['units_per_strip', 'location_section', 'location_column']);
        });
    }
};
