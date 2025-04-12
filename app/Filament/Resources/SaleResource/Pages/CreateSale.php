<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;
    
    // Di sini kita tidak perlu mutateFormDataBeforeCreate karena kita override handleRecordCreation
    
    // Override record creation untuk mengambil kendali total
    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // Extract items data
            $itemsData = $data['items_data'] ?? [];
            unset($data['items_data']);
            
            // Create the sale record without items first
            $sale = new Sale();
            $sale->fill($data);
            $sale->save();
            
            // Calculate totals and create items
            $totalAmount = 0;
            $totalCost = 0;
            
            // Create sale items and update product stock
            if (!empty($itemsData)) {
                foreach ($itemsData as $itemData) {
                    // Get product
                    $product = null;
                    if (isset($itemData['product_id'])) {
                        $product = Product::find($itemData['product_id']);
                    }
                    
                    if (!$product) {
                        continue; // Skip invalid products
                    }
                    
                    // Ensure all required fields are present
                    $saleItemData = [
                        'sale_id' => $sale->id,
                        'product_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity'] ?? 1,
                        'price' => $itemData['price'] ?? $product->selling_price,
                        'purchase_price' => $itemData['purchase_price'] ?? $product->purchase_price,
                        'total_price' => $itemData['total_price'] ?? ($itemData['price'] * $itemData['quantity']),
                        'total_purchase_price' => $itemData['total_purchase_price'] ?? ($itemData['purchase_price'] * $itemData['quantity']),
                    ];
                    
                    // Create the item directly - not using relationship
                    $item = new SaleItem();
                    $item->fill($saleItemData);
                    $item->save();
                    
                    // Update running totals
                    $totalAmount += $item->total_price;
                    $totalCost += $item->total_purchase_price;
                    
                    // Update product stock
                    $product->sold += $item->quantity;
                    $product->stock -= $item->quantity;
                    $product->save();
                }
            }
            
            // Update sale totals
            $sale->total_amount = $totalAmount;
            $sale->cost_amount = $totalCost;
            $sale->change_amount = max(0, $sale->payment_amount - $totalAmount);
            $sale->save();
            
            // Refresh the sale to get all relationships
            $sale->refresh();
            
            return $sale;
        });
    }
    
    protected function afterCreate(): void
    {
        $sale = $this->record;
        
        // Check for any inventory issues
        foreach ($sale->items as $item) {
            $product = $item->product;
            if ($product && $product->stock < 0) {
                Notification::make()
                    ->title('Stock Issue Detected')
                    ->body("The product {$product->name} has a negative stock value. Please check inventory.")
                    ->danger()
                    ->send();
            }
        }
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}