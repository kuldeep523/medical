<?php

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Store;
use App\Models\User;
use App\Models\Supplier;
use App\Livewire\SrExpiry;
use Livewire\Livewire;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    // Setup Store
    $this->store = Store::create([
        'store_name' => 'Test Pharmacy Store',
        'owner_name' => 'Store Owner',
        'email' => 'storetest@example.com',
        'status' => 'active'
    ]);

    // Setup Admin
    $this->admin = User::create([
        'name' => 'Store Admin',
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
        'role' => 'store_admin',
        'store_id' => $this->store->id
    ]);

    $this->supplier = Supplier::create([
        'name' => 'Apex Pharma Distributor',
        'email' => 'apex@example.com',
        'phone' => '1234567890',
        'address' => 'Distributor Zone 1',
        'store_id' => $this->store->id,
        'user_id' => $this->admin->id
    ]);

    $this->medicine = Medicine::create([
        'name' => 'Metformin',
        'rx_salt' => 'Metformin Hydrochloride',
        'power_mg' => '500mg',
        'brand_name' => 'Glucophage',
        'expiry_date' => now()->addYear(),
        'location_section' => 'Shelf A',
        'location_column' => 'Row 2',
        'user_id' => $this->admin->id,
        'store_id' => $this->store->id
    ]);
});

test('SrExpiry component loads stats and lists batches correctly', function () {
    $this->actingAs($this->admin);

    // 1. Expired batch (3 days ago)
    MedicineBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_no' => 'EXP-001',
        'expiry_date' => Carbon::today()->subDays(3),
        'quantity' => 15,
        'purchase_price' => 10,
        'sales_price' => 15,
        'user_id' => $this->admin->id,
        'store_id' => $this->store->id,
        'vendor_name' => 'Apex Pharma Distributor'
    ]);

    // 2. Near expiry batch (30 days from now)
    MedicineBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_no' => 'NEAR-002',
        'expiry_date' => Carbon::today()->addDays(30),
        'quantity' => 20,
        'purchase_price' => 20,
        'sales_price' => 30,
        'user_id' => $this->admin->id,
        'store_id' => $this->store->id,
        'vendor_name' => 'Apex Pharma Distributor'
    ]);

    Livewire::test(SrExpiry::class)
        ->assertViewHas('batches')
        ->assertViewHas('stats', function ($stats) {
            return $stats['expired_count'] === 1 
                && $stats['near_expiry_count'] === 1 
                && $stats['returnable_value'] == (15 * 10 + 20 * 20); // 150 + 400 = 550
        });
});

test('SrExpiry marking batch as returned works and clears quantity', function () {
    $this->actingAs($this->admin);

    $batch = MedicineBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_no' => 'EXP-005',
        'expiry_date' => Carbon::today()->subDays(10),
        'quantity' => 50,
        'purchase_price' => 8,
        'sales_price' => 12,
        'user_id' => $this->admin->id,
        'store_id' => $this->store->id,
        'vendor_name' => 'Apex Pharma Distributor'
    ]);

    Livewire::test(SrExpiry::class)
        ->call('markReturned', $batch->id)
        ->assertHasNoErrors();

    $batch->refresh();
    expect($batch->return_status)->toBe('returned_to_vendor');
    expect($batch->quantity)->toBe(0);
});

test('SrExpiry bulk return action works correctly', function () {
    $this->actingAs($this->admin);

    // Expired Batch
    $batch1 = MedicineBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_no' => 'B-EXP',
        'expiry_date' => Carbon::today()->subDays(5),
        'quantity' => 10,
        'purchase_price' => 5,
        'sales_price' => 8,
        'user_id' => $this->admin->id,
        'store_id' => $this->store->id
    ]);

    // Near Expiry Batch
    $batch2 = MedicineBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_no' => 'B-NEAR',
        'expiry_date' => Carbon::today()->addDays(20),
        'quantity' => 20,
        'purchase_price' => 5,
        'sales_price' => 8,
        'user_id' => $this->admin->id,
        'store_id' => $this->store->id
    ]);

    // Normal Batch (should NOT be returned)
    $batch3 = MedicineBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_no' => 'B-NORMAL',
        'expiry_date' => Carbon::today()->addYear(),
        'quantity' => 100,
        'purchase_price' => 5,
        'sales_price' => 8,
        'user_id' => $this->admin->id,
        'store_id' => $this->store->id
    ]);

    Livewire::test(SrExpiry::class)
        ->call('bulkMarkReturned')
        ->assertHasNoErrors();

    expect($batch1->refresh()->return_status)->toBe('returned_to_vendor');
    expect($batch1->quantity)->toBe(0);

    expect($batch2->refresh()->return_status)->toBe('returned_to_vendor');
    expect($batch2->quantity)->toBe(0);

    expect(in_array($batch3->refresh()->return_status, ['none', null], true))->toBeTrue();
    expect($batch3->quantity)->toBe(100);
});
