<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSale extends ViewRecord
{
    protected static string $resource = SaleResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('print')
                ->label('Print Receipt')
                ->color('primary')
                ->url(fn () => route('admin.sales.print', ['sale' => $this->record]))
                ->openUrlInNewTab(),
        ];
    }
}