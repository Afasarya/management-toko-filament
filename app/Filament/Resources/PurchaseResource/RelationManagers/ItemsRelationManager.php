<?php

namespace App\Filament\Resources\PurchaseResource\RelationManagers;

use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
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
                    ->relationship('product', 'name')
                    ->required()
                    ->preload()
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function (Set $set, $state) {
                        $product = Product::find($state);
                        if ($product) {
                            $set('price', $product->purchase_price);
                        }
                    }),
                    
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->live(),
                    
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get) {
                        $price = $get('price');
                        $quantity = $get('quantity');
                        $set('total_price', $price * $quantity);
                    }),
                    
                Forms\Components\TextInput::make('total_price')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
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
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function (array $data, $livewire): ?\Illuminate\Database\Eloquent\Model {
                        // Update purchase total amount
                        $purchase = $livewire->getOwnerRecord();
                        $purchase->total_amount += $data['total_price'];
                        $purchase->save();
                        
                        // Update product stock
                        $product = Product::find($data['product_id']);
                        if ($product) {
                            $product->purchased += $data['quantity'];
                            $product->stock += $data['quantity'];
                            $product->save();
                        }
                        
                        return $this->getRelationship()->create($data);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->using(function (\Illuminate\Database\Eloquent\Model $record, array $data, $livewire): \Illuminate\Database\Eloquent\Model {
                        // Get the original values
                        $originalQuantity = $record->quantity;
                        $originalTotalPrice = $record->total_price;
                        
                        // Update purchase total amount
                        $purchase = $livewire->getOwnerRecord();
                        $purchase->total_amount = $purchase->total_amount - $originalTotalPrice + $data['total_price'];
                        $purchase->save();
                        
                        // Update product stock
                        $product = $record->product;
                        if ($product) {
                            $product->purchased = $product->purchased - $originalQuantity + $data['quantity'];
                            $product->stock = $product->stock - $originalQuantity + $data['quantity'];
                            $product->save();
                        }
                        
                        // Update the record
                        $record->update($data);
                        
                        return $record;
                    }),
                    
                Tables\Actions\DeleteAction::make()
                    ->using(function (Model $record, $livewire): bool {
                        // Update purchase total amount
                        $purchase = $livewire->getOwnerRecord();
                        $purchase->total_amount -= $record->total_price;
                        $purchase->save();
                        
                        // Update product stock
                        $product = $record->product;
                        if ($product) {
                            $product->purchased -= $record->quantity;
                            $product->stock -= $record->quantity;
                            $product->save();
                        }
                        
                        return $record->delete();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->using(function ($records, $livewire): void {
                            $purchase = $livewire->getOwnerRecord();
                            $totalAmount = 0;
                            
                            foreach ($records as $record) {
                                // Update product stock
                                $product = $record->product;
                                if ($product) {
                                    $product->purchased -= $record->quantity;
                                    $product->stock -= $record->quantity;
                                    $product->save();
                                }
                                
                                $totalAmount += $record->total_price;
                                $record->delete();
                            }
                            
                            // Update purchase total amount
                            $purchase->total_amount -= $totalAmount;
                            $purchase->save();
                        }),
                ]),
            ]);
    }
}