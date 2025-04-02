<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            ['code' => '0001', 'name' => 'Rei'],
            ['code' => '0002', 'name' => 'Eiger'],
            ['code' => '0003', 'name' => 'Swalow'],
            ['code' => '0004', 'name' => 'Adidas'],
            ['code' => '0005', 'name' => 'Nike'],
            ['code' => '0006', 'name' => 'Puma'],
            ['code' => '0007', 'name' => 'Kaosid'],
            ['code' => '0008', 'name' => 'Akukos'],
            ['code' => '0009', 'name' => 'C&M'],
            ['code' => '0010', 'name' => 'Rucass'],
        ];
        
        foreach ($brands as $brand) {
            Brand::create($brand);
        }
    }
}