<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use App\Models\Product;
use App\Models\Purchase;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;
    
    // Intercept form data before model creation
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Calculate total amount from items
        $totalAmount = 0;
        
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                if (isset($item['total_price'])) {
                    $totalAmount += (int)$item['total_price'];
                }
            }
        }
        
        // Ensure total_amount is explicitly set
        $data['total_amount'] = $totalAmount;
        
        return $data;
    }
    
    // Override the default record creation to use transactions
    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // Create the purchase with an explicit total amount
            $record = static::getModel()::create($data);
            
            // For extra safety, update the total_amount directly
            if (!empty($record->items)) {
                $calculatedTotal = $record->items->sum('total_price');
                if ($calculatedTotal > 0 && $record->total_amount != $calculatedTotal) {
                    $record->total_amount = $calculatedTotal;
                    $record->save();
                }
            }
            
            return $record;
        });
    }
    
    // Update product stocks after purchase is created
    protected function afterCreate(): void
    {
        $purchase = $this->record;
        
        DB::transaction(function () use ($purchase) {
            // Recalculate total_amount one more time to be 100% sure
            $actualTotal = $purchase->items->sum('total_price');
            if ($purchase->total_amount != $actualTotal) {
                $purchase->total_amount = $actualTotal;
                $purchase->save();
            }
            
            // Update product stocks for each item
            foreach ($purchase->items as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->purchased += $item->quantity;
                    $product->stock += $item->quantity;
                    $product->save();
                }
            }
        });
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}