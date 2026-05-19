<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin user
        User::firstOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'Main Admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Sample normal user
        User::firstOrCreate([
            'email' => 'user@example.com',
        ], [
            'name' => 'Dr. Jane Smith',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
    }
}
