<?php

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Medicine;
use App\Models\Store;
use App\Models\User;
use App\Livewire\AccountingMis;
use Livewire\Livewire;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->store = Store::create([
        'store_name' => 'Accounting Test Pharmacy',
        'owner_name' => 'Store Owner',
        'email' => 'accountingowner@example.com',
        'status' => 'active'
    ]);

    $this->admin = User::create([
        'name' => 'Store Admin',
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
        'role' => 'store_admin',
        'store_id' => $this->store->id
    ]);

    $this->medicine = Medicine::create([
        'name' => 'Paracetamol 500mg',
        'rx_salt' => 'Paracetamol',
        'purpose' => 'Fever',
        'power_mg' => '500',
        'brand_name' => 'Cipla',
        'units_per_strip' => 10,
        'reorder_point' => 5,
        'location_section' => 'A',
        'location_column' => '1',
        'user_id' => $this->admin->id,
        'store_id' => $this->store->id
    ]);
});

test('AccountingMis component allows viewing and closing completed sales bills', function () {
    $this->actingAs($this->admin);

    $sale = Sale::create([
        'user_id' => $this->admin->id,
        'store_id' => $this->store->id,
        'customer_name' => 'Johnny Appleseed',
        'bill_no' => 'BILL-SAL-999',
        'total_amount' => 100,
        'amount_paid' => 100,
        'payment_method' => 'Cash'
    ]);

    SaleItem::create([
        'sale_id' => $sale->id,
        'medicine_id' => $this->medicine->id,
        'batch_no' => 'B-112',
        'quantity' => 10,
        'purchase_price' => 8,
        'price' => 10,
        'total' => 100
    ]);

    Livewire::test(AccountingMis::class)
        ->call('viewSaleBill', $sale->id)
        ->assertSet('isSaleModalOpen', true)
        ->assertSet('selectedSale.id', $sale->id)
        ->call('closeSaleModal')
        ->assertSet('isSaleModalOpen', false)
        ->assertSet('selectedSale', null);
});
