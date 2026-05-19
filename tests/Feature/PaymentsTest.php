<?php

use App\Models\Purchase;
use App\Models\Store;
use App\Models\User;
use App\Models\Supplier;
use App\Livewire\Payments;
use Livewire\Livewire;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->store = Store::create([
        'store_name' => 'Payments Test Pharmacy',
        'owner_name' => 'Store Owner',
        'email' => 'paymentsowner@example.com',
        'status' => 'active'
    ]);

    $this->admin = User::create([
        'name' => 'Store Admin',
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
        'role' => 'store_admin',
        'store_id' => $this->store->id
    ]);

    $this->supplier = Supplier::create([
        'name' => 'Grand Pharma',
        'user_id' => $this->admin->id,
        'store_id' => $this->store->id,
        'phone' => '1234567890'
    ]);
});

test('Payments component loads stats and lists pending purchases correctly', function () {
    $this->actingAs($this->admin);

    // 1. Pending Purchase (total = 1000, paid = 400, due = 600)
    Purchase::create([
        'user_id' => $this->admin->id,
        'store_id' => $this->store->id,
        'supplier_id' => $this->supplier->id,
        'bill_number' => 'PUR-2001',
        'bill_date' => now()->toDateString(),
        'total_amount' => 1000,
        'paid_amount' => 400,
        'payment_mode' => 'Cash'
    ]);

    // 2. Fully cleared Purchase
    Purchase::create([
        'user_id' => $this->admin->id,
        'store_id' => $this->store->id,
        'supplier_id' => $this->supplier->id,
        'bill_number' => 'PUR-2002',
        'bill_date' => now()->toDateString(),
        'total_amount' => 500,
        'paid_amount' => 500,
        'payment_mode' => 'UPI',
        'dues_cleared_at' => now()
    ]);

    Livewire::test(Payments::class)
        ->assertViewHas('purchases')
        ->assertViewHas('stats', function ($stats) {
            return $stats['total_payables'] == 600
                && $stats['pending_suppliers'] === 1;
        });
});

test('Payments component can clear full vendor dues', function () {
    $this->actingAs($this->admin);

    $purchase = Purchase::create([
        'user_id' => $this->admin->id,
        'store_id' => $this->store->id,
        'supplier_id' => $this->supplier->id,
        'bill_number' => 'PUR-3001',
        'bill_date' => now()->toDateString(),
        'total_amount' => 1200,
        'paid_amount' => 200,
        'payment_mode' => 'Cash'
    ]);

    Livewire::test(Payments::class)
        ->call('clearFullDues', $purchase->id)
        ->assertHasNoErrors();

    $purchase->refresh();
    expect($purchase->paid_amount)->toBe(1200.00);
    expect($purchase->dues_cleared_at)->not->toBeNull();
});

test('Payments component can record partial payments to vendor', function () {
    $this->actingAs($this->admin);

    $purchase = Purchase::create([
        'user_id' => $this->admin->id,
        'store_id' => $this->store->id,
        'supplier_id' => $this->supplier->id,
        'bill_number' => 'PUR-4001',
        'bill_date' => now()->toDateString(),
        'total_amount' => 1000,
        'paid_amount' => 500,
        'payment_mode' => 'Cash'
    ]);

    // Apply partial payment of 300
    Livewire::test(Payments::class)
        ->set('amountToPay', 300)
        ->set('paymentMethod', 'UPI')
        ->call('recordPartialPayment', $purchase->id)
        ->assertHasNoErrors();

    $purchase->refresh();
    expect($purchase->paid_amount)->toBe(800.00);
    expect($purchase->payment_mode)->toBe('UPI');
    expect($purchase->dues_cleared_at)->toBeNull();

    // Apply remainder partial payment of 200 (clearing it)
    Livewire::test(Payments::class)
        ->set('amountToPay', 200)
        ->set('paymentMethod', 'Cash')
        ->call('recordPartialPayment', $purchase->id)
        ->assertHasNoErrors();

    $purchase->refresh();
    expect($purchase->paid_amount)->toBe(1000.00);
    expect($purchase->dues_cleared_at)->not->toBeNull();
});
