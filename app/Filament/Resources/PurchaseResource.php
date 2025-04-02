<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Filament\Resources\PurchaseResource\RelationManagers;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    
    protected static ?string $navigationGroup = 'Transactions';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Purchase Information')
                    ->schema([
                        Forms\Components\TextInput::make('invoice_number')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->default(function () {
                                $lastPurchase = Purchase::orderBy('id', 'desc')->first();
                                $lastId = $lastPurchase ? $lastPurchase->id : 0;
                                $nextId = str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);
                                return $nextId;
                            }),
                            
                        Forms\Components\DatePicker::make('purchase_date')
                            ->required()
                            ->default(now()),
                            
                        Forms\Components\Select::make('supplier_id')
                            ->label('Supplier')
                            ->relationship('supplier', 'name')
                            ->required()
                            ->preload()
                            ->searchable()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('code')
                                    ->required()
                                    ->maxLength(20)
                                    ->unique(Supplier::class),
                                    
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(50),
                                    
                                Forms\Components\DatePicker::make('registration_date')
                                    ->required()
                                    ->default(now()),
                                    
                                Forms\Components\TextInput::make('phone')
                                    ->tel()
                                    ->maxLength(20),
                                    
                                Forms\Components\Textarea::make('address')
                                    ->maxLength(255),
                            ]),
                            
                        Forms\Components\Select::make('user_id')
                            ->label('Purchaser')
                            ->relationship('user', 'name')
                            ->required()
                            ->default(fn() => \Illuminate\Support\Facades\Auth::id())
                            ->preload()
                            ->searchable(),
                            
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Purchase Items')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Product')
                                    ->relationship('product', 'name')
                                    ->required()
                                    ->preload()
                                    ->searchable()
                                    ->distinct()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
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
                            ])
                            ->columns(4)
                            ->itemLabel(fn (array $state): ?string => $state['product_id'] ? Product::find($state['product_id'])?->name : null)
                            ->reorderable(false)
                            ->addActionLabel('Add Product')
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $items = $get('items');
                                $totalAmount = 0;
                                
                                if (is_array($items)) {
                                    foreach ($items as $item) {
                                        if (isset($item['total_price'])) {
                                            $totalAmount += $item['total_price'];
                                        }
                                    }
                                }
                                
                                $set('total_amount', $totalAmount);
                            })
                            ->required()
                            ->minItems(1),
                            
                        Forms\Components\TextInput::make('total_amount')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->default(0),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('purchase_date')
                    ->date()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('supplier.name')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('IDR')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Purchaser')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('item_count')
                    ->label('Items')
                    ->badge(),
                    
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
                Tables\Filters\SelectFilter::make('supplier')
                    ->relationship('supplier', 'name')
                    ->preload()
                    ->searchable(),
                    
                Tables\Filters\Filter::make('purchase_date')
                    ->form([
                        Forms\Components\DatePicker::make('purchase_date')
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data) {
                        if ($data['purchase_date']) {
                            $query->whereDate('purchase_date', $data['purchase_date']);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->before(function (Purchase $record) {
                        // Revert stock updates
                        foreach ($record->items as $item) {
                            $product = $item->product;
                            $product->purchased -= $item->quantity;
                            $product->stock -= $item->quantity;
                            $product->save();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            // Revert stock updates for all deleted records
                            foreach ($records as $record) {
                                foreach ($record->items as $item) {
                                    $product = $item->product;
                                    $product->purchased -= $item->quantity;
                                    $product->stock -= $item->quantity;
                                    $product->save();
                                }
                            }
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            'view' => Pages\ViewPurchase::route('/{record}'),
            'edit' => Pages\EditPurchase::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereDate('purchase_date', today())->count() ?: null;
    }
}