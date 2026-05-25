<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Store;
use App\Models\User;
use App\Livewire\PosSystem;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

function assertTrue($condition, $message) {
    if (!$condition) {
        throw new \Exception($message);
    }
}

// Use a transaction that rolls back to not dirty the DB
DB::beginTransaction();

try {
    echo "Starting POS Vendor Column Verification...\n";

    // 1. Create a dummy store, user, medicine, and batches
    $store = Store::create([
        'store_name' => 'Verify POS Pharmacy',
        'owner_name' => 'Store Owner',
        'email' => 'posverify@example.com',
        'status' => 'active'
    ]);

    $admin = User::create([
        'name' => 'Store Admin',
        'email' => 'admin_verify@example.com',
        'password' => Hash::make('password'),
        'role' => 'store_admin',
        'store_id' => $store->id
    ]);

    // Login the user so the store global scope applies
    auth()->login($admin);

    $medicine = Medicine::create([
        'name' => 'Paracetamol 500mg',
        'rx_salt' => 'Paracetamol',
        'purpose' => 'Fever',
        'power_mg' => '500',
        'brand_name' => 'Cipla',
        'units_per_strip' => 10,
        'reorder_point' => 5,
        'location_section' => 'A',
        'location_column' => '1',
        'user_id' => $admin->id,
        'store_id' => $store->id
    ]);

    // Create batches
    $batch1 = MedicineBatch::create([
        'medicine_id' => $medicine->id,
        'batch_no' => 'V-B1',
        'expiry_date' => '2027-12-31',
        'quantity' => 100,
        'purchase_price' => 8.0,
        'sales_price' => 10.0,
        'vendor_name' => 'Super Pharma Supplier',
        'user_id' => $admin->id,
        'store_id' => $store->id
    ]);

    // 2. Instantiate Livewire Component and test methods
    $component = new PosSystem();
    $component->mount();

    $component->selectMedicine($medicine->id);
    
    // Select batch
    $component->selectedBatchId = $batch1->id;
    $component->updatedSelectedBatchId($batch1->id);

    // After updatedSelectedBatchId is called, the item is added to the cart
    assertTrue(count($component->cart) === 1, "Cart should have 1 item");
    $item = $component->cart[0];
    
    // Verify vendor name exists in cart item
    assertTrue($item['vendor_name'] === 'Super Pharma Supplier', "Cart item vendor name mismatch. Got: " . $item['vendor_name']);
    echo "✓ Cart item stores vendor_name correctly.\n";

    echo "\nALL TESTS PASSED SUCCESSFULLY!\n";
} catch (\Throwable $e) {
    echo "\nTEST FAILED: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
} finally {
    DB::rollBack();
}
