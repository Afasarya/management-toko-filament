<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin user
        User::create([
            'username' => 'admin',
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'address' => 'Jl. Admin No. 1',
            'phone' => '08123456789',
            'birth_date' => now()->subYears(30),
            'active_date' => now(),
            'role' => 'admin',
        ]);
        
        // Create Cashier user
        User::create([
            'username' => 'kasir',
            'name' => 'Kasir',
            'email' => 'kasir@example.com',
            'password' => Hash::make('password'),
            'address' => 'Jl. Kasir No. 1',
            'phone' => '08987654321',
            'birth_date' => now()->subYears(25),
            'active_date' => now(),
            'role' => 'kasir',
        ]);
    }
}