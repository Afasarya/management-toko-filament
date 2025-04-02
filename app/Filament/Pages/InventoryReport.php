<?php

namespace App\Filament\Pages;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class InventoryReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.inventory-report';

    // Define public properties for form fields
    public $category_id = null;
    public $brand_id = null;
    public $stock_status = 'all';

    public function mount(): void
    {
        $this->form->fill([
            'category_id' => $this->category_id,
            'brand_id' => $this->brand_id,
            'stock_status' => $this->stock_status,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('category_id')
                            ->label('Category')
                            ->options(Category::pluck('name', 'id'))
                            ->placeholder('All Categories')
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->category_id = $state;
                            })
                            ->searchable(),
                            
                        Select::make('brand_id')
                            ->label('Brand')
                            ->options(Brand::pluck('name', 'id'))
                            ->placeholder('All Brands')
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->brand_id = $state;
                            })
                            ->searchable(),
                            
                        Select::make('stock_status')
                            ->label('Stock Status')
                            ->options([
                                'all' => 'All Products',
                                'in_stock' => 'In Stock',
                                'low_stock' => 'Low Stock',
                                'out_of_stock' => 'Out of Stock',
                            ])
                            ->default('all')
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->stock_status = $state;
                            }),
                    ])->columns(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function (Builder $query) {
                $query = Product::query();
                
                if ($this->category_id) {
                    $query->where('category_id', $this->category_id);
                }
                
                if ($this->brand_id) {
                    $query->where('brand_id', $this->brand_id);
                }
                
                if ($this->stock_status === 'in_stock') {
                    $query->where('stock', '>', 0);
                } elseif ($this->stock_status === 'low_stock') {
                    $query->whereColumn('stock', '<=', 'min_stock')->where('stock', '>', 0);
                } elseif ($this->stock_status === 'out_of_stock') {
                    $query->where('stock', '=', 0);
                }
                
                return $query->orderBy('name');
            })
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->wrap(),
                    
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
                    ->color(fn (Product $record): string => $record->isLowStock() ? 'danger' : 'success')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make(),
                    ]),
                    
                Tables\Columns\TextColumn::make('calculated_value')
                    ->state(function (Product $record): int {
                        return $record->stock * $record->purchase_price;
                    })
                    ->label('Inventory Value')
                    ->money('IDR')
                    ->alignEnd(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (Product $record): string => route('filament.admin.resources.products.view', ['record' => $record]))
                    ->icon('heroicon-m-eye')
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                //
            ])
            ->emptyStateIcon('heroicon-o-cube')
            ->emptyStateHeading('No products found')
            ->emptyStateDescription('Products will appear here when created.');
    }
    
    public function getInventorySummary(): array
    {
        $query = Product::query();
        
        if ($this->category_id) {
            $query->where('category_id', $this->category_id);
        }
        
        if ($this->brand_id) {
            $query->where('brand_id', $this->brand_id);
        }
        
        // We need to clone the query for different counts
        $baseQuery = clone $query;
        $lowStockQuery = clone $query;
        $outOfStockQuery = clone $query;
        
        // Apply stock status filter for the main query if set
        if ($this->stock_status === 'in_stock') {
            $query->where('stock', '>', 0);
        } elseif ($this->stock_status === 'low_stock') {
            $query->whereColumn('stock', '<=', 'min_stock')->where('stock', '>', 0);
        } elseif ($this->stock_status === 'out_of_stock') {
            $query->where('stock', '=', 0);
        }
        
        $totalProducts = $query->count();
        $totalItems = $query->sum('stock');
        
        // Calculate total value using a DB sum() to avoid memory issues
        $totalValue = $query->sum(DB::raw('stock * purchase_price'));
        
        // Get counts for low stock and out of stock regardless of filter
        $lowStockProducts = $lowStockQuery->whereColumn('stock', '<=', 'min_stock')
            ->where('stock', '>', 0)
            ->count();
        
        $outOfStockProducts = $outOfStockQuery->where('stock', '=', 0)->count();
        
        return [
            'total_products' => $totalProducts,
            'total_items' => $totalItems,
            'total_value' => $totalValue,
            'low_stock_products' => $lowStockProducts,
            'out_of_stock_products' => $outOfStockProducts,
        ];
    }
    
    public function getViewData(): array
    {
        return [
            'inventorySummary' => $this->getInventorySummary(),
        ];
    }
    
    public function printReport()
    {
        return redirect()->route('admin.reports.inventory.print', [
            'category_id' => $this->category_id,
            'brand_id' => $this->brand_id,
            'stock_status' => $this->stock_status,
        ]);
    }
}