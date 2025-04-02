<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['code' => '0001', 'name' => 'Baju'],
            ['code' => '0002', 'name' => 'Celana'],
            ['code' => '0003', 'name' => 'Topi'],
            ['code' => '0004', 'name' => 'Sepatu'],
            ['code' => '0005', 'name' => 'Kaos Kaki'],
            ['code' => '0006', 'name' => 'Sendal'],
            ['code' => '0007', 'name' => 'Tas'],
        ];
        
        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}