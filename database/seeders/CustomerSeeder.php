<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'code' => '00001',
                'name' => 'Vero',
                'address' => 'Wangon',
                'phone' => '09441142342',
                'registration_date' => '2025-02-17',
            ],
        ];
        
        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}