<?php

define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

DB::beginTransaction();

try {
    $store = App\Models\Store::create([
        'store_name' => 'Verify Store', 'owner_name' => 'Owner',
        'email' => 'verify2@example.com', 'status' => 'active'
    ]);
    $user = App\Models\User::create([
        'name' => 'Verify User', 'email' => 'verify2@example.com',
        'password' => bcrypt('password'), 'role' => 'store_admin', 'store_id' => $store->id
    ]);
    auth()->login($user);

    // Create medicine: 10 tablets per strip
    $medicine = App\Models\Medicine::create([
        'name' => 'Strip Test Med', 'rx_salt' => 'TestSalt', 'power_mg' => '10mg',
        'brand_name' => 'Brand', 'units_per_strip' => 10, 'reorder_point' => 5,
        'location_section' => 'A', 'location_column' => '1',
        'user_id' => $user->id, 'store_id' => $store->id
    ]);

    // Batch: sales_price = 13 (unit price, stored correctly after our fix)
    $batch = App\Models\MedicineBatch::create([
        'medicine_id' => $medicine->id, 'batch_no' => 'TEST01',
        'expiry_date' => '2028-01-01', 'quantity' => 10,
        'purchase_price' => 10.0, 'sales_price' => 13.0,
        'user_id' => $user->id, 'store_id' => $store->id
    ]);

    echo "=== NEW CODE VERIFICATION ===\n";
    echo "Medicine: units_per_strip = {$medicine->units_per_strip}\n";
    echo "Batch: sales_price (per unit) = {$batch->sales_price}\n\n";

    // Simulate selectMedicine + addToCart
    $pos = new App\Livewire\PosSystem();

    // Manually simulate selectMedicine
    $unitsPerStrip = max(1, $medicine->units_per_strip ?? 1);
    $pos->selectedMedicine = $medicine;
    $pos->selectedBatch = $batch;
    $pos->inputQuantity = 1;
    $pos->inputPrice = $batch->sales_price; // NEW: per-unit price directly

    // Simulate addToCart
    $pos->addToCart();

    echo "Cart after selecting 1 unit:\n";
    $item = $pos->cart[0];
    echo "  price (MRP/TAB input): " . $item['price'] . " (Expected: 13 - per tablet)\n";
    echo "  unit_price: " . $item['unit_price'] . " (Expected: 13)\n";
    echo "  quantity: " . $item['quantity'] . " (Expected: 1)\n";
    echo "  strips: " . $item['strips'] . " (Expected: 0)\n";
    echo "  tablets: " . $item['tablets'] . " (Expected: 1)\n";
    echo "  total: " . $item['total'] . " (Expected: 13)\n\n";

    if ($item['total'] != 13) throw new Exception("FAIL: total should be 13 for 1 tablet at 13/tab");

    // Simulate updatedCart when user sets strips=0, tablets=1
    $pos->cart[0]['strips'] = 0;
    $pos->cart[0]['tablets'] = 1;
    $pos->updatedCart(1, '0.tablets');

    $item = $pos->cart[0];
    echo "After updatedCart (0 strips + 1 tablet):\n";
    echo "  quantity: " . $item['quantity'] . " (Expected: 1)\n";
    echo "  unit_price: " . $item['unit_price'] . " (Expected: 13)\n";
    echo "  total: " . $item['total'] . " (Expected: 13)\n\n";

    if ($item['total'] != 13) throw new Exception("FAIL: total should be 13 for 0 strips + 1 tablet");

    // Simulate 1 strip + 1 tablet = 11 tablets
    $pos->cart[0]['strips'] = 1;
    $pos->cart[0]['tablets'] = 1;
    $pos->updatedCart(1, '0.strips');

    $item = $pos->cart[0];
    echo "After updatedCart (1 strip + 1 tablet = 11 tablets):\n";
    echo "  quantity: " . $item['quantity'] . " (Expected: 11)\n";
    echo "  total: " . $item['total'] . " (Expected: 143)\n\n";

    if ($item['quantity'] != 11) throw new Exception("FAIL: quantity should be 11");
    if ($item['total'] != 143.0) throw new Exception("FAIL: total should be 143 for 11 tablets at 13/tab");

    echo "=== ALL TESTS PASSED! ===\n";
    echo "\nSummary:\n";
    echo "  1 tablet -> MRP/TAB shows 13, Amount = 13 Rs\n";
    echo "  0 strips + 1 tablet -> Amount = 13 Rs\n";
    echo "  1 strip + 1 tablet (11 tabs) -> Amount = 143 Rs\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
} finally {
    DB::rollBack();
}
