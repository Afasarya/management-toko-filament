<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use App\Models\Product;
use App\Models\PurchaseItem;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Collection;

class EditPurchase extends EditRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->before(function () {
                    // Revert stock updates
                    foreach ($this->record->items as $item) {
                        $product = $item->product;
                        $product->purchased -= $item->quantity;
                        $product->stock -= $item->quantity;
                        $product->save();
                    }
                }),
        ];
    }
    
    protected function afterSave(): void
    {
        // Get the purchase before and after the update
        $purchase = $this->record;
        
        // Update products stock based on the changes in purchase items
        // Note: This is handled within the items relation manager
        // We just need to ensure the total amount is correct
        $total = $purchase->items->sum('total_price');
        if ($purchase->total_amount != $total) {
            $purchase->total_amount = $total;
            $purchase->saveQuietly();
        }
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}