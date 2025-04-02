<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get categories and brands to use their IDs
        $categories = Category::all()->keyBy('name');
        $brands = Brand::all()->keyBy('name');
        
        $products = [
            [
                'code' => '000001',
                'sku' => 'BRG000001',
                'name' => 'Baju Polos',
                'purchase_price' => 100000,
                'selling_price' => 150000,
                'description' => 'Masih ori',
                'category_id' => $categories['Baju']->id,
                'brand_id' => $brands['Rucass']->id,
                'sold' => 2,
                'purchased' => 107,
                'stock' => 105,
                'min_stock' => 50,
            ],
            [
                'code' => '000002',
                'sku' => 'BRG000002',
                'name' => 'Baju Kotra',
                'purchase_price' => 25000,
                'selling_price' => 50000,
                'description' => '',
                'category_id' => $categories['Baju']->id,
                'brand_id' => $brands['C&M']->id,
                'sold' => 3,
                'purchased' => 102,
                'stock' => 99,
                'min_stock' => 1,
            ],
            [
                'code' => '000006',
                'sku' => 'BRG000006',
                'name' => 'Celana Jeans',
                'purchase_price' => 450000,
                'selling_price' => 500000,
                'description' => '',
                'category_id' => $categories['Celana']->id,
                'brand_id' => $brands['Rucass']->id,
                'sold' => 6,
                'purchased' => 100,
                'stock' => 94,
                'min_stock' => 67,
            ],
            [
                'code' => '000016',
                'sku' => 'BRG000016',
                'name' => 'Jordan',
                'purchase_price' => 500000,
                'selling_price' => 900000,
                'description' => '',
                'category_id' => $categories['Sepatu']->id,
                'brand_id' => $brands['Nike']->id,
                'sold' => 2,
                'purchased' => 102,
                'stock' => 100,
                'min_stock' => 30,
            ],
            [
                'code' => '000018',
                'sku' => 'BRG000018',
                'name' => 'LowJord',
                'purchase_price' => 500000,
                'selling_price' => 800000,
                'description' => '',
                'category_id' => $categories['Sepatu']->id,
                'brand_id' => $brands['Rei']->id,
                'sold' => 1,
                'purchased' => 100,
                'stock' => 99,
                'min_stock' => 19,
            ],
        ];
        
        foreach ($products as $product) {
            Product::create($product);
                }
        
            }
        }