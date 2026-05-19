<?php

use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\Store;
use App\Models\User;
use App\Models\Supplier;
use App\Livewire\Ledger;
use Livewire\Livewire;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->store = Store::create([
        'store_name' => 'Ledger Test Pharmacy',
        'owner_name' => 'Store Owner',
        'email' => 'ledgerowner@example.com',
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
        'name' => 'Acme Dist',
        'user_id' => $this->admin->id,
        'store_id' => $this->store->id,
        'phone' => '1234567890'
    ]);
});

test('Ledger component loads successfully', function () {
    $this->actingAs($this->admin);

    Livewire::test(Ledger::class)
        ->assertViewHas('suppliers')
        ->assertViewHas('customers')
        ->assertViewHas('expenseCategories');
});

test('Ledger component computes correct supplier entries', function () {
    $this->actingAs($this->admin);

    // Create a purchase (total = 1000, paid = 400)
    Purchase::create([
        'user_id' => $this->admin->id,
        'store_id' => $this->store->id,
        'supplier_id' => $this->supplier->id,
        'bill_number' => 'BILL-8001',
        'bill_date' => now()->toDateString(),
        'total_amount' => 1000,
        'paid_amount' => 400,
        'payment_mode' => 'Cash'
    ]);

    Livewire::test(Ledger::class)
        ->set('accountType', 'supplier')
        ->set('supplierId', $this->supplier->id)
        ->assertViewHas('closingBalance', 600.0);
});

test('Ledger component computes correct customer entries', function () {
    $this->actingAs($this->admin);

    // Create a sale (total = 1200, paid = 800)
    Sale::create([
        'user_id' => $this->admin->id,
        'store_id' => $this->store->id,
        'customer_name' => 'Steve Jobs',
        'bill_no' => 'BILL-9001',
        'total_amount' => 1200,
        'amount_paid' => 800,
        'payment_method' => 'Cash'
    ]);

    Livewire::test(Ledger::class)
        ->set('accountType', 'customer')
        ->set('customerName', 'Steve Jobs')
        ->assertViewHas('closingBalance', 400.0);
});
