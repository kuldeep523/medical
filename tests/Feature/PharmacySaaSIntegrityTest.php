<?php

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Store;
use App\Models\User;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Livewire\PosSystem;
use App\Livewire\PharmacyPortal;
use Livewire\Livewire;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    // Setup Stores
    $this->storeA = Store::create([
        'store_name' => 'Store A',
        'owner_name' => 'Owner A',
        'email' => 'storea@example.com',
        'status' => 'active'
    ]);

    $this->storeB = Store::create([
        'store_name' => 'Store B',
        'owner_name' => 'Owner B',
        'email' => 'storeb@example.com',
        'status' => 'active'
    ]);

    // Setup Admins
    $this->adminA = User::create([
        'name' => 'Admin A',
        'email' => 'admina@example.com',
        'password' => Hash::make('password'),
        'role' => 'store_admin',
        'store_id' => $this->storeA->id
    ]);

    $this->adminB = User::create([
        'name' => 'Admin B',
        'email' => 'adminb@example.com',
        'password' => Hash::make('password'),
        'role' => 'store_admin',
        'store_id' => $this->storeB->id
    ]);

    $this->superAdmin = User::create([
        'name' => 'Super Admin',
        'email' => 'super@example.com',
        'password' => Hash::make('password'),
        'role' => 'admin'
    ]);
});

test('multi-tenant leakage: Store B cannot see Store A medicines', function () {
    // Create medicine in Store A
    Medicine::create([
        'name' => 'Med A',
        'rx_salt' => 'Salt A',
        'power_mg' => '500mg',
        'brand_name' => 'Brand A',
        'expiry_date' => now()->addYear(),
        'location_section' => 'A1',
        'location_column' => 'C1',
        'user_id' => $this->adminA->id,
        'store_id' => $this->storeA->id
    ]);

    // Login as Store B admin
    $this->actingAs($this->adminB);

    // Verify medicines count is 0 for Store B (due to global scope)
    expect(Medicine::count())->toBe(0);
});

test('batch inventory integrity: multi-batch deduction FEFO logic', function () {
    $this->actingAs($this->adminA);

    $medicine = Medicine::create([
        'name' => 'Inventory Med',
        'rx_salt' => 'Salt',
        'power_mg' => '100mg',
        'brand_name' => 'Brand',
        'expiry_date' => now()->addYear(),
        'location_section' => 'A1',
        'location_column' => 'C1',
        'user_id' => $this->adminA->id,
        'store_id' => $this->storeA->id
    ]);

    // Batch 1: Expiring soon (10 units)
    $batch1 = MedicineBatch::create([
        'medicine_id' => $medicine->id,
        'batch_no' => 'B1',
        'expiry_date' => now()->addDays(10),
        'quantity' => 10,
        'purchase_price' => 50,
        'sales_price' => 100,
        'user_id' => $this->adminA->id,
        'store_id' => $this->storeA->id
    ]);

    // Batch 2: Expiring later (5 units)
    $batch2 = MedicineBatch::create([
        'medicine_id' => $medicine->id,
        'batch_no' => 'B2',
        'expiry_date' => now()->addDays(20),
        'quantity' => 5,
        'purchase_price' => 50,
        'sales_price' => 100,
        'user_id' => $this->adminA->id,
        'store_id' => $this->storeA->id
    ]);

    // Perform sale of 12 units using POS
    Livewire::test(PosSystem::class)
        ->set('searchQuery', 'Inventory Med')
        ->call('selectMedicine', $medicine->id)
        ->set('inputQuantity', 12)
        ->call('addToCart')
        ->call('checkout');

    // Verify quantities
    expect($batch1->fresh()->quantity)->toBe(0);
    expect($batch2->fresh()->quantity)->toBe(3);
});

test('loose sale and rounding: strips and tablets math', function () {
    $this->actingAs($this->adminA);

    $medicine = Medicine::create([
        'name' => 'Strip Med',
        'rx_salt' => 'Salt',
        'power_mg' => '10mg',
        'units_per_strip' => 15, // 15 tablets per strip
        'brand_name' => 'Brand',
        'expiry_date' => now()->addYear(),
        'location_section' => 'A1',
        'location_column' => 'C1',
        'user_id' => $this->adminA->id,
        'store_id' => $this->storeA->id
    ]);

    // Add 1 strip (15 units) priced at 143
    MedicineBatch::create([
        'medicine_id' => $medicine->id,
        'batch_no' => 'STRIP-01',
        'expiry_date' => now()->addYear(),
        'quantity' => 15,
        'purchase_price' => 100 / 15,
        'sales_price' => 143 / 15, // Price per unit
        'user_id' => $this->adminA->id,
        'store_id' => $this->storeA->id
    ]);

    // Simulate sale of 2 loose tablets
    $unitPrice = 143 / 15;
    $expectedTotal = round(2 * $unitPrice, 2);

    Livewire::test(PosSystem::class)
        ->call('selectMedicine', $medicine->id)
        ->set('inputQuantity', 2)
        ->call('addToCart')
        ->call('checkout');

    // Verify grand total
    $lastSale = Sale::latest()->first();
    expect(round($lastSale->total_amount, 2))->toBe($expectedTotal);

    // Verify remaining stock display
    expect($medicine->fresh()->formatted_stock)->toBe('0 strips, 13 tablets');
});

test('payment mode and accounting: cash and upi tracking', function () {
    $this->actingAs($this->adminA);

    $medicine = Medicine::create([
        'name' => 'Payment Med',
        'rx_salt' => 'Salt',
        'power_mg' => '500mg',
        'brand_name' => 'Brand',
        'expiry_date' => now()->addYear(),
        'location_section' => 'A1',
        'location_column' => 'C1',
        'user_id' => $this->adminA->id,
        'store_id' => $this->storeA->id
    ]);

    MedicineBatch::create([
        'medicine_id' => $medicine->id,
        'batch_no' => 'PAY-01',
        'expiry_date' => now()->addYear(),
        'quantity' => 100,
        'purchase_price' => 50,
        'sales_price' => 100,
        'user_id' => $this->adminA->id,
        'store_id' => $this->storeA->id
    ]);

    // Cash Sale (100)
    Livewire::test(PosSystem::class)
        ->call('selectMedicine', $medicine->id)
        ->set('inputQuantity', 1)
        ->set('payment_method', 'Cash')
        ->call('addToCart')
        ->call('checkout');

    // UPI Sale (200)
    Livewire::test(PosSystem::class)
        ->call('selectMedicine', $medicine->id)
        ->set('inputQuantity', 2)
        ->set('payment_method', 'Bank/UPI')
        ->call('addToCart')
        ->call('checkout');

    // Verify Sale totals
    expect(Sale::where('payment_method', 'Cash')->sum('total_amount'))->toEqual(100);
    expect(Sale::where('payment_method', 'Bank/UPI')->sum('total_amount'))->toEqual(200);
});

test('security: Store Admin cannot access Super Admin page', function () {
    // Store Admin trying to access /admin/dashboard
    $this->actingAs($this->adminA);

    $response = $this->get('/admin/dashboard');

    $response->assertStatus(403);
});
