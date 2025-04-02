<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LowStockProducts extends BaseWidget
{
    protected static ?int $sort = 4;
    
    protected int|string|array $columnSpan = 'full';
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->whereColumn('stock', '<=', 'min_stock')
                    ->orderBy('stock')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->limit(30),
                    
                Tables\Columns\TextColumn::make('category.name')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('brand.name')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('stock')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('danger'),
                    
                Tables\Columns\TextColumn::make('min_stock')
                    ->numeric()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('restock')
                    ->url(fn (Product $record): string => route('filament.admin.resources.products.edit', ['record' => $record]))
                    ->icon('heroicon-m-plus-circle')
                    ->button()
                    ->label('Restock')
                    ->color('warning'),
            ]);
    }
}