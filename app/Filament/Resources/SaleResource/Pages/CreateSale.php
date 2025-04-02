<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use App\Models\Product;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;
    
    protected function afterCreate(): void
    {
        $sale = $this->record;
        
        // Update product stocks
        // This is already handled in the form processing, but we double-check here
        foreach ($sale->items as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                // Make sure the stock and sold values are correct
                if ($product->stock < 0) {
                    Notification::make()
                        ->title('Stock Issue Detected')
                        ->body("The product {$product->name} has a negative stock value. Please check inventory.")
                        ->danger()
                        ->send();
                }
            }
        }
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}