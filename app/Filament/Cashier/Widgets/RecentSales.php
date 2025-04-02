<?php

namespace App\Filament\Cashier\Widgets;

use App\Models\Sale;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RecentSales extends BaseWidget
{
    protected static ?int $sort = 2;
    
    protected int|string|array $columnSpan = 'full';
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Sale::query()
                    ->where('user_id', Auth::id())
                    ->latest('sale_date')
                    ->limit(10)
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
                    
                Tables\Columns\TextColumn::make('item_count')
                    ->label('Items')
                    ->badge(),
            ])
            ->actions([
                Tables\Actions\Action::make('print')
                    ->url(fn (Sale $record): string => route('admin.sales.print', $record))
                    ->icon('heroicon-m-printer')
                    ->openUrlInNewTab(),
            ]);
    }
}