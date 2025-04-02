<?php

namespace App\Filament\Resources\SaleResource\RelationManagers;

use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'name', function (Builder $query) {
                        return $query->where('stock', '>', 0);
                    })
                    ->required()
                    ->preload()
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function (Set $set, $state) {
                        $product = Product::find($state);
                        if ($product) {
                            $set('price', $product->selling_price);
                            $set('purchase_price', $product->purchase_price);
                            $set('max_quantity', $product->stock);
                        }
                    }),
                    
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get) {
                        $price = $get('price');
                        $quantity = $get('quantity');
                        $set('total_price', $price * $quantity);
                    }),
                    
                Forms\Components\TextInput::make('purchase_price')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->hidden(),
                    
                Forms\Components\TextInput::make('max_quantity')
                    ->numeric()
                    ->hidden(),
                    
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get) {
                        $price = $get('price');
                        $purchasePrice = $get('purchase_price');
                        $quantity = $get('quantity');
                        $set('total_price', $price * $quantity);
                        $set('total_purchase_price', $purchasePrice * $quantity);
                    }),
                    
                Forms\Components\TextInput::make('total_price')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled()
                    ->default(0),
                    
                Forms\Components\TextInput::make('total_purchase_price')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->hidden(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product.name')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('price')
                    ->money('IDR')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('quantity')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('total_price')
                    ->money('IDR')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('profit')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function (array $data, $livewire): Model {
                        // Check if product has enough stock
                        $product = Product::find($data['product_id']);
                        if ($product && $product->stock < $data['quantity']) {
                            throw new \Exception("Not enough stock for {$product->name}. Available: {$product->stock}");
                        }
                        
                        // Update sale total amount
                        $sale = $livewire->getOwnerRecord();
                        $sale->total_amount += $data['total_price'];
                        $sale->cost_amount += $data['total_purchase_price'];
                        $sale->save();
                        
                        // Update product stock
                        if ($product) {
                            $product->sold += $data['quantity'];
                            $product->stock -= $data['quantity'];
                            $product->save();
                        }
                        
                        return $this->getRelationship()->create($data);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->using(function (Model $record, array $data, $livewire): Model {
                        // Get the original values
                        $originalQuantity = $record->quantity;
                        $originalTotalPrice = $record->total_price;
                        $originalTotalPurchasePrice = $record->total_purchase_price;
                        
                        // Check if product has enough stock for the new quantity
                        $product = $record->product;
                        $availableStock = $product->stock + $originalQuantity; // Add back the original quantity
                        
                        if ($availableStock < $data['quantity']) {
                            throw new \Exception("Not enough stock for {$product->name}. Available: {$availableStock}");
                        }
                        
                        // Update sale total amount
                        $sale = $livewire->getOwnerRecord();
                        $sale->total_amount = $sale->total_amount - $originalTotalPrice + $data['total_price'];
                        $sale->cost_amount = $sale->cost_amount - $originalTotalPurchasePrice + $data['total_purchase_price'];
                        $sale->save();
                        
                        // Update product stock
                        if ($product) {
                            $product->sold = $product->sold - $originalQuantity + $data['quantity'];
                            $product->stock = $product->stock + $originalQuantity - $data['quantity'];
                            $product->save();
                        }
                        
                        // Update the record
                        $record->update($data);
                        
                        return $record;
                    }),
                    
                Tables\Actions\DeleteAction::make()
                    ->using(function (Model $record, $livewire): bool {
                        // Update sale total amount
                        $sale = $livewire->getOwnerRecord();
                        $sale->total_amount -= $record->total_price;
                        $sale->cost_amount -= $record->total_purchase_price;
                        $sale->save();
                        
                        // Update product stock
                        $product = $record->product;
                        if ($product) {
                            $product->sold -= $record->quantity;
                            $product->stock += $record->quantity;
                            $product->save();
                        }
                        
                        return $record->delete();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->using(function ($records, $livewire): void {
                            $sale = $livewire->getOwnerRecord();
                            $totalAmount = 0;
                            $totalCostAmount = 0;
                            
                            foreach ($records as $record) {
                                // Update product stock
                                $product = $record->product;
                                if ($product) {
                                    $product->sold -= $record->quantity;
                                    $product->stock += $record->quantity;
                                    $product->save();
                                }
                                
                                $totalAmount += $record->total_price;
                                $totalCostAmount += $record->total_purchase_price;
                                $record->delete();
                            }
                            
                            // Update sale total amount
                            $sale->total_amount -= $totalAmount;
                            $sale->cost_amount -= $totalCostAmount;
                            $sale->save();
                        }),
                ]),
            ]);
    }
}