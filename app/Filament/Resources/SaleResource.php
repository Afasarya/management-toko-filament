<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Filament\Resources\SaleResource\RelationManagers;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    
    protected static ?string $navigationGroup = 'Transactions';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Sale Information')
                    ->schema([
                        Forms\Components\TextInput::make('invoice_number')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->default(function () {
                                $lastSale = Sale::orderBy('id', 'desc')->first();
                                $lastId = $lastSale ? $lastSale->id : 0;
                                $nextId = str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);
                                return $nextId;
                            }),
                            
                        Forms\Components\DatePicker::make('sale_date')
                            ->required()
                            ->default(now()),
                            
                        Forms\Components\Select::make('customer_id')
                            ->label('Customer')
                            ->relationship('customer', 'name')
                            ->preload()
                            ->searchable()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('code')
                                    ->required()
                                    ->maxLength(20)
                                    ->unique(Customer::class),
                                    
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
                            ])
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if (!$state) {
                                    $set('customer_name', 'Umum');
                                } else {
                                    $customer = Customer::find($state);
                                    $set('customer_name', $customer?->name ?? 'Umum');
                                }
                            }),
                            
                        Forms\Components\TextInput::make('customer_name')
                            ->required()
                            ->maxLength(50)
                            ->default('Umum'),
                            
                        Forms\Components\Select::make('user_id')
                            ->label('Cashier')
                            ->relationship('user', 'name')
                            ->required()
                            ->default(fn() => \Illuminate\Support\Facades\Auth::id())
                            ->preload()
                            ->searchable(),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Sale Items')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Product')
                                    ->relationship('product', 'name', function (Builder $query) {
                                        return $query->where('stock', '>', 0);
                                    })
                                    ->required()
                                    ->preload()
                                    ->searchable()
                                    ->distinct()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
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
                                    })
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('check')
                                            ->icon('heroicon-m-information-circle')
                                            ->tooltip(fn (Get $get): string => 'Available: ' . ($get('max_quantity') ?? 0))
                                    ),
                                    
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
                            ])
                            ->columns(3)
                            ->itemLabel(fn (array $state): ?string => $state['product_id'] ? Product::find($state['product_id'])?->name : null)
                            ->reorderable(false)
                            ->addActionLabel('Add Product')
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $items = $get('items');
                                $totalAmount = 0;
                                $totalCost = 0;
                                
                                if (is_array($items)) {
                                    foreach ($items as $item) {
                                        if (isset($item['total_price'])) {
                                            $totalAmount += $item['total_price'];
                                            $totalCost += $item['total_purchase_price'] ?? 0;
                                        }
                                    }
                                }
                                
                                $set('total_amount', $totalAmount);
                                $set('cost_amount', $totalCost);
                                $set('payment_amount', $totalAmount);
                                $set('change_amount', 0);
                            })
                            ->required()
                            ->minItems(1),
                    ]),
                    
                Forms\Components\Section::make('Payment Details')
                    ->schema([
                        Forms\Components\TextInput::make('total_amount')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->default(0),
                            
                        Forms\Components\TextInput::make('cost_amount')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->hidden(),
                            
                        Forms\Components\TextInput::make('payment_amount')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                $payment = $get('payment_amount');
                                $total = $get('total_amount');
                                $set('change_amount', max(0, $payment - $total));
                            }),
                            
                        Forms\Components\TextInput::make('change_amount')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->default(0),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->preload()
                    ->searchable()
                    ->label('Cashier'),
                    
                Tables\Filters\Filter::make('sale_date')
                    ->form([
                        Forms\Components\DatePicker::make('sale_date_start')
                            ->label('Sale Date From'),
                        Forms\Components\DatePicker::make('sale_date_end')
                            ->label('Sale Date Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['sale_date_start'], fn ($query, $date) => $query->whereDate('sale_date', '>=', $date))
                            ->when($data['sale_date_end'], fn ($query, $date) => $query->whereDate('sale_date', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->before(function (Sale $record) {
                        // Revert stock updates
                        foreach ($record->items as $item) {
                            $product = $item->product;
                            $product->sold -= $item->quantity;
                            $product->stock += $item->quantity;
                            $product->save();
                        }
                    }),
                    
                Tables\Actions\Action::make('print')
                    ->label('Print')
                    ->color('success')
                    ->icon('heroicon-m-printer')
                    ->url(fn (Sale $record): string => route('admin.sales.print', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            // Revert stock updates for all deleted records
                            foreach ($records as $record) {
                                foreach ($record->items as $item) {
                                    $product = $item->product;
                                    $product->sold -= $item->quantity;
                                    $product->stock += $item->quantity;
                                    $product->save();
                                }
                            }
                        }),
                ]),
            ]);
    }
    
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Sale Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('invoice_number'),
                        Infolists\Components\TextEntry::make('sale_date')
                            ->date(),
                        Infolists\Components\TextEntry::make('customer_name'),
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Cashier'),
                    ])->columns(2),
                    
                Infolists\Components\Section::make('Sale Items')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->schema([
                                Infolists\Components\TextEntry::make('product.name'),
                                Infolists\Components\TextEntry::make('price')
                                    ->money('IDR'),
                                Infolists\Components\TextEntry::make('quantity'),
                                Infolists\Components\TextEntry::make('total_price')
                                    ->money('IDR'),
                            ])->columns(4),
                    ]),
                    
                Infolists\Components\Section::make('Payment Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('total_amount')
                            ->money('IDR'),
                        Infolists\Components\TextEntry::make('payment_amount')
                            ->money('IDR'),
                        Infolists\Components\TextEntry::make('change_amount')
                            ->money('IDR'),
                        Infolists\Components\TextEntry::make('profit')
                            ->money('IDR'),
                    ])->columns(2),
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
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'view' => Pages\ViewSale::route('/{record}'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereDate('sale_date', today())->count() ?: null;
    }
}