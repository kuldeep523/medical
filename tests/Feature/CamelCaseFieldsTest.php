<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Doctor;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CamelCaseFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_fields_are_capitalized_on_save()
    {
        $doctor = Doctor::create([
            'store_id' => 1,
            'name' => 'dr. john doe',
            'specialization' => 'cardiology expert',
            'phone' => '1234567890', // should NOT be formatted
            'email' => 'doctor@example.com', // should NOT be formatted
            'clinic_name' => 'central heart clinic',
            'clinic_address' => '123 Main St, New York',
            'registration_no' => 'REG-12345',
            'is_active' => true,
        ]);

        $this->assertEquals('Dr. John Doe', $doctor->name);
        $this->assertEquals('Cardiology Expert', $doctor->specialization);
        $this->assertEquals('Central Heart Clinic', $doctor->clinic_name);
        $this->assertEquals('123 Main St, New York', $doctor->clinic_address);
        // Non-formatted fields should remain untouched
        $this->assertEquals('1234567890', $doctor->phone);
        $this->assertEquals('doctor@example.com', $doctor->email);
    }

    public function test_store_fields_are_capitalized_on_save()
    {
        $store = Store::create([
            'store_name' => 'my custom pharmacy',
            'owner_name' => 'jane doe smith',
            'address' => '456 Oak Road',
            'email' => 'pharmacy@example.com',
            'gst_number' => 'GST12345',
            'status' => 'active',
        ]);

        $this->assertEquals('My Custom Pharmacy', $store->store_name);
        $this->assertEquals('Jane Doe Smith', $store->owner_name);
        $this->assertEquals('456 Oak Road', $store->address);
        $this->assertEquals('pharmacy@example.com', $store->email);
    }

    public function test_user_fields_are_capitalized_on_save()
    {
        $user = User::create([
            'name' => 'dr. jane smith',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'store_id' => 1,
        ]);

        $this->assertEquals('Dr. Jane Smith', $user->name);
        $this->assertEquals('jane@example.com', $user->email);
    }
}
