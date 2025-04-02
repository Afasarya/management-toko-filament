<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestSales extends BaseWidget
{
    protected static ?int $sort = 3;
    
    protected int|string|array $columnSpan = 'full';
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Sale::query()
                    ->latest('sale_date')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('sale_date')
                    ->date()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('customer_name')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('IDR')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('profit')
                    ->money('IDR')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Cashier')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('item_count')
                    ->label('Items')
                    ->badge(),
            ]);
    }
}