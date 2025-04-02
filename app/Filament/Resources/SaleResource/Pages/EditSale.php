<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;

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
                        $product->sold -= $item->quantity;
                        $product->stock += $item->quantity;
                        $product->save();
                    }
                }),
        ];
    }
    
    protected function afterSave(): void
    {
        // Get the sale before and after the update
        $sale = $this->record;
        
        // Update totals if needed - this is mainly handled within the items relation manager
        // We just need to ensure the total amount is correct
        $totalPrice = $sale->items->sum('total_price');
        $totalCost = $sale->items->sum('total_purchase_price');
        
        if ($sale->total_amount != $totalPrice || $sale->cost_amount != $totalCost) {
            $sale->total_amount = $totalPrice;
            $sale->cost_amount = $totalCost;
            $sale->saveQuietly();
        }
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}