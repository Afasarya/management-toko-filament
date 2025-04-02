<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use App\Models\Product;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;
    
    protected function afterCreate(): void
    {
        $purchase = $this->record;
        
        // Update product stocks
        foreach ($purchase->items as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $product->purchased += $item->quantity;
                $product->stock += $item->quantity;
                $product->save();
            }
        }
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}