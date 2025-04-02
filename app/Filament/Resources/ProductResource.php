<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    
    protected static ?string $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Product Information')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true),
                            
                        Forms\Components\TextInput::make('sku')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true),
                            
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(100),
                            
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->required()
                            ->preload()
                            ->searchable(),
                            
                        Forms\Components\Select::make('brand_id')
                            ->relationship('brand', 'name')
                            ->required()
                            ->preload()
                            ->searchable(),
                            
                        Forms\Components\Textarea::make('description')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Pricing & Stock')
                    ->schema([
                        Forms\Components\TextInput::make('purchase_price')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                            
                        Forms\Components\TextInput::make('selling_price')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                            
                        Forms\Components\TextInput::make('stock')
                            ->required()
                            ->numeric()
                            ->default(0),
                            
                        Forms\Components\TextInput::make('min_stock')
                            ->required()
                            ->numeric()
                            ->default(0),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                    
                Tables\Columns\TextColumn::make('purchase_price')
                    ->money('IDR')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('selling_price')
                    ->money('IDR')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('stock')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (Product $record): string => $record->isLowStock() ? 'danger' : 'success'),
                    
                Tables\Columns\TextColumn::make('min_stock')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->preload()
                    ->searchable(),
                    
                Tables\Filters\SelectFilter::make('brand')
                    ->relationship('brand', 'name')
                    ->preload()
                    ->searchable(),
                    
                Tables\Filters\Filter::make('low_stock')
                    ->label('Low Stock')
                    ->query(fn (Builder $query) => $query->whereColumn('stock', '<=', 'min_stock')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Product Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('code'),
                        Infolists\Components\TextEntry::make('sku'),
                        Infolists\Components\TextEntry::make('name'),
                        Infolists\Components\TextEntry::make('category.name'),
                        Infolists\Components\TextEntry::make('brand.name'),
                        Infolists\Components\TextEntry::make('description')
                            ->columnSpanFull(),
                    ])->columns(2),
                    
                Infolists\Components\Section::make('Pricing & Stock')
                    ->schema([
                        Infolists\Components\TextEntry::make('purchase_price')
                            ->money('IDR'),
                            
                        Infolists\Components\TextEntry::make('selling_price')
                            ->money('IDR'),
                            
                        Infolists\Components\TextEntry::make('getProfit')
                            ->label('Profit')
                            ->money('IDR'),
                            
                        Infolists\Components\TextEntry::make('getProfitPercentage')
                            ->label('Profit %')
                            ->suffix('%'),
                            
                        Infolists\Components\TextEntry::make('stock')
                            ->badge()
                            ->color(fn (Product $record): string => $record->isLowStock() ? 'danger' : 'success'),
                            
                        Infolists\Components\TextEntry::make('min_stock'),
                            
                        Infolists\Components\TextEntry::make('getTotalValue')
                            ->label('Inventory Value')
                            ->money('IDR')
                            ->columnSpanFull(),
                    ])->columns(2),
                    
                Infolists\Components\Section::make('Sales & Purchase History')
                    ->schema([
                        Infolists\Components\TextEntry::make('sold')
                            ->label('Total Sold'),
                            
                        Infolists\Components\TextEntry::make('purchased')
                            ->label('Total Purchased'),
                    ])->columns(2),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}