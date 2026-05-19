<?php

use App\Models\Sale;
use App\Models\Store;
use App\Models\User;
use App\Livewire\Receipts;
use Livewire\Livewire;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->store = Store::create([
        'store_name' => 'Receipts Test Pharmacy',
        'owner_name' => 'Store Owner',
        'email' => 'receiptsowner@example.com',
        'status' => 'active'
    ]);

    $this->admin = User::create([
        'name' => 'Store Admin',
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
        'role' => 'store_admin',
        'store_id' => $this->store->id
    ]);
});

test('Receipts component loads stats and lists pending sales correctly', function () {
    $this->actingAs($this->admin);

    // 1. Pending sale
    Sale::create([
        'user_id' => $this->admin->id,
        'store_id' => $this->store->id,
        'bill_no' => 'BILL-1001',
        'total_amount' => 1000,
        'amount_paid' => 400,
        'payment_method' => 'Cash',
        'customer_name' => 'John Doe'
    ]);

    // 2. Fully cleared sale
    Sale::create([
        'user_id' => $this->admin->id,
        'store_id' => $this->store->id,
        'bill_no' => 'BILL-1002',
        'total_amount' => 500,
        'amount_paid' => 500,
        'payment_method' => 'UPI',
        'customer_name' => 'Jane Smith',
        'dues_cleared_at' => now()
    ]);

    Livewire::test(Receipts::class)
        ->assertViewHas('sales')
        ->assertViewHas('stats', function ($stats) {
            return $stats['total_receivables'] == 600
                && $stats['pending_customers'] === 1;
        });
});

test('Receipts component can clear full dues', function () {
    $this->actingAs($this->admin);

    $sale = Sale::create([
        'user_id' => $this->admin->id,
        'store_id' => $this->store->id,
        'bill_no' => 'BILL-2001',
        'total_amount' => 1200,
        'amount_paid' => 200,
        'payment_method' => 'Cash',
        'customer_name' => 'Alan Turing'
    ]);

    Livewire::test(Receipts::class)
        ->call('clearFullDues', $sale->id)
        ->assertHasNoErrors();

    $sale->refresh();
    expect($sale->amount_paid)->toBe(1200.00);
    expect($sale->dues_cleared_at)->not->toBeNull();
});

test('Receipts component can record partial payments', function () {
    $this->actingAs($this->admin);

    $sale = Sale::create([
        'user_id' => $this->admin->id,
        'store_id' => $this->store->id,
        'bill_no' => 'BILL-3001',
        'total_amount' => 1000,
        'amount_paid' => 500,
        'payment_method' => 'Cash',
        'customer_name' => 'Grace Hopper'
    ]);

    // Apply partial payment of 300
    Livewire::test(Receipts::class)
        ->set('amountToPay', 300)
        ->set('paymentMethod', 'UPI')
        ->call('recordPartialPayment', $sale->id)
        ->assertHasNoErrors();

    $sale->refresh();
    expect($sale->amount_paid)->toBe(800.00);
    expect($sale->payment_method)->toBe('UPI');
    expect($sale->dues_cleared_at)->toBeNull();

    // Apply remainder partial payment of 200 (clearing it)
    Livewire::test(Receipts::class)
        ->set('amountToPay', 200)
        ->set('paymentMethod', 'Cash')
        ->call('recordPartialPayment', $sale->id)
        ->assertHasNoErrors();

    $sale->refresh();
    expect($sale->amount_paid)->toBe(1000.00);
    expect($sale->dues_cleared_at)->not->toBeNull();
});
