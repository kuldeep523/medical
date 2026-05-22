<?php

putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=:memory:');
$_ENV['DB_CONNECTION'] = 'sqlite';
$_ENV['DB_DATABASE'] = ':memory:';
$_SERVER['DB_CONNECTION'] = 'sqlite';
$_SERVER['DB_DATABASE'] = ':memory:';

define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Doctor;
use App\Models\Store;
use App\Models\User;
use App\Models\Medicine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

// Migrate the database
Artisan::call('migrate');

DB::beginTransaction();

try {
    $store = Store::create([
        'store_name' => 'Verify Store Name',
        'owner_name' => 'owner john doe',
        'email' => 'verify-camel@example.com',
        'address' => '123 test address road',
        'status' => 'active'
    ]);

    $user = User::create([
        'name' => 'user name here',
        'email' => 'verify-camel-user@example.com',
        'password' => bcrypt('password'),
        'role' => 'store_admin',
        'store_id' => $store->id
    ]);

    $doctor = Doctor::create([
        'store_id' => $store->id,
        'name' => 'dr. john smith',
        'specialization' => 'heart surgeon specialist',
        'phone' => '1234567890',
        'email' => 'doctor@example.com',
        'clinic_name' => 'apex clinic hospital',
        'clinic_address' => '789 medical street',
        'registration_no' => 'REG-999',
        'is_active' => true,
    ]);

    $medicine = Medicine::create([
        'name' => 'paracetamol extra 500mg',
        'rx_salt' => 'paracetamol active',
        'purpose' => 'fever relief',
        'power_mg' => '500mg',
        'brand_name' => 'gsk pharmaceuticals',
        'units_per_strip' => 10,
        'reorder_point' => 5,
        'location_section' => 'section a',
        'location_column' => 'column b',
        'user_id' => $user->id,
        'store_id' => $store->id
    ]);

    echo "=== CAMEL CASE VERIFICATION RESULTS ===\n";
    
    echo "Store store_name: '{$store->store_name}' (Expected: 'verifyStoreName')\n";
    echo "Store owner_name: '{$store->owner_name}' (Expected: 'ownerJohnDoe')\n";
    echo "Store address: '{$store->address}' (Expected: '123TestAddressRoad')\n";
    echo "User name: '{$user->name}' (Expected: 'userNameHere')\n";
    echo "Doctor name: '{$doctor->name}' (Expected: 'drJohnSmith')\n";
    echo "Doctor specialization: '{$doctor->specialization}' (Expected: 'heartSurgeonSpecialist')\n";
    echo "Doctor clinic_name: '{$doctor->clinic_name}' (Expected: 'apexClinicHospital')\n";
    echo "Doctor clinic_address: '{$doctor->clinic_address}' (Expected: '789MedicalStreet')\n";
    echo "Medicine name: '{$medicine->name}' (Expected: 'paracetamolExtra500mg')\n";
    echo "Medicine rx_salt: '{$medicine->rx_salt}' (Expected: 'paracetamolActive')\n";
    echo "Medicine purpose: '{$medicine->purpose}' (Expected: 'feverRelief')\n";
    echo "Medicine brand_name: '{$medicine->brand_name}' (Expected: 'gskPharmaceuticals')\n";
    echo "Medicine location_section: '{$medicine->location_section}' (Expected: 'sectionA')\n";
    echo "Medicine location_column: '{$medicine->location_column}' (Expected: 'columnB')\n";

    if ($store->store_name !== 'verifyStoreName') throw new Exception("FAIL: Store store_name");
    if ($store->owner_name !== 'ownerJohnDoe') throw new Exception("FAIL: Store owner_name");
    if ($store->address !== '123TestAddressRoad') throw new Exception("FAIL: Store address");
    if ($user->name !== 'userNameHere') throw new Exception("FAIL: User name");
    if ($doctor->name !== 'drJohnSmith') throw new Exception("FAIL: Doctor name");
    if ($doctor->specialization !== 'heartSurgeonSpecialist') throw new Exception("FAIL: Doctor specialization");
    if ($doctor->clinic_name !== 'apexClinicHospital') throw new Exception("FAIL: Doctor clinic_name");
    if ($doctor->clinic_address !== '789MedicalStreet') throw new Exception("FAIL: Doctor clinic_address");
    if ($medicine->name !== 'paracetamolExtra500mg') throw new Exception("FAIL: Medicine name");
    if ($medicine->rx_salt !== 'paracetamolActive') throw new Exception("FAIL: Medicine rx_salt");
    if ($medicine->purpose !== 'feverRelief') throw new Exception("FAIL: Medicine purpose");
    if ($medicine->brand_name !== 'gskPharmaceuticals') throw new Exception("FAIL: Medicine brand_name");
    if ($medicine->location_section !== 'sectionA') throw new Exception("FAIL: Medicine location_section");
    if ($medicine->location_column !== 'columnB') throw new Exception("FAIL: Medicine location_column");

    echo "\n=== ALL VERIFICATION CHECKS PASSED! ===\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
} finally {
    DB::rollBack();
}
