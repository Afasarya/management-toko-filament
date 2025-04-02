<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'code' => '0001',
                'name' => 'Indomart',
                'address' => 'Wangon',
                'phone' => '0812143611231',
                'registration_date' => '2025-02-17',
            ],
            [
                'code' => '0002',
                'name' => 'Alfamart',
                'address' => 'Cilacap',
                'phone' => '0818673511231',
                'registration_date' => '2025-01-01',
            ],
            [
                'code' => '0003',
                'name' => '7 Eleven',
                'address' => 'Bandung',
                'phone' => '01731543123',
                'registration_date' => '2025-02-05',
            ],
            [
                'code' => '0004',
                'name' => 'Mallo',
                'address' => 'Wonosobo',
                'phone' => '0813615341',
                'registration_date' => '2024-12-17',
            ],
        ];
        
        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}