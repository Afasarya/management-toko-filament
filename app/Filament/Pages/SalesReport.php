<?php

namespace App\Filament\Pages;

use App\Models\Sale;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
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

class SalesReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.sales-report';

    // Properti publik untuk menampung data form
    public $period = 'today';
    public $start_date;
    public $end_date;

    public function mount(): void
    {
        $this->start_date = now()->startOfMonth()->format('Y-m-d');
        $this->end_date = now()->format('Y-m-d');
        
        $this->form->fill([
            'period' => $this->period,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('period')
                            ->label('Period')
                            ->options([
                                'today' => 'Today',
                                'yesterday' => 'Yesterday',
                                'this_week' => 'This Week',
                                'last_week' => 'Last Week',
                                'this_month' => 'This Month',
                                'last_month' => 'Last Month',
                                'this_year' => 'This Year',
                                'custom' => 'Custom Range',
                            ])
                            ->default('today')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $this->period = $state;
                                
                                if ($state === 'today') {
                                    $this->start_date = now()->format('Y-m-d');
                                    $this->end_date = now()->format('Y-m-d');
                                    $set('start_date', $this->start_date);
                                    $set('end_date', $this->end_date);
                                } elseif ($state === 'yesterday') {
                                    $this->start_date = now()->subDay()->format('Y-m-d');
                                    $this->end_date = now()->subDay()->format('Y-m-d');
                                    $set('start_date', $this->start_date);
                                    $set('end_date', $this->end_date);
                                } elseif ($state === 'this_week') {
                                    $this->start_date = now()->startOfWeek()->format('Y-m-d');
                                    $this->end_date = now()->endOfWeek()->format('Y-m-d');
                                    $set('start_date', $this->start_date);
                                    $set('end_date', $this->end_date);
                                } elseif ($state === 'last_week') {
                                    $this->start_date = now()->subWeek()->startOfWeek()->format('Y-m-d');
                                    $this->end_date = now()->subWeek()->endOfWeek()->format('Y-m-d');
                                    $set('start_date', $this->start_date);
                                    $set('end_date', $this->end_date);
                                } elseif ($state === 'this_month') {
                                    $this->start_date = now()->startOfMonth()->format('Y-m-d');
                                    $this->end_date = now()->endOfMonth()->format('Y-m-d');
                                    $set('start_date', $this->start_date);
                                    $set('end_date', $this->end_date);
                                } elseif ($state === 'last_month') {
                                    $this->start_date = now()->subMonth()->startOfMonth()->format('Y-m-d');
                                    $this->end_date = now()->subMonth()->endOfMonth()->format('Y-m-d');
                                    $set('start_date', $this->start_date);
                                    $set('end_date', $this->end_date);
                                } elseif ($state === 'this_year') {
                                    $this->start_date = now()->startOfYear()->format('Y-m-d');
                                    $this->end_date = now()->endOfYear()->format('Y-m-d');
                                    $set('start_date', $this->start_date);
                                    $set('end_date', $this->end_date);
                                }
                            }),
                            
                        DatePicker::make('start_date')
                            ->label('Start Date')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->start_date = $state;
                            })
                            ->visible(fn ($get) => $get('period') === 'custom'),
                            
                        DatePicker::make('end_date')
                            ->label('End Date')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->end_date = $state;
                            })
                            ->minDate(fn ($get) => $get('start_date'))
                            ->visible(fn ($get) => $get('period') === 'custom'),
                    ])->columns(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function (Builder $query) {
                $startDate = Carbon::parse($this->start_date)->startOfDay();
                $endDate = Carbon::parse($this->end_date)->endOfDay();
                
                // Add a calculated column for profit to the query
                $query = Sale::query()
                    ->select([
                        'sales.*',
                        DB::raw('(total_amount - cost_amount) as calculated_profit')
                    ])
                    ->whereBetween('sale_date', [$startDate, $endDate])
                    ->orderBy('sale_date', 'desc');
                
                return $query;
            })
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
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('IDR'),
                    ]),
                    
                Tables\Columns\TextColumn::make('cost_amount')
                    ->money('IDR')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('IDR'),
                    ]),
                    
                Tables\Columns\TextColumn::make('calculated_profit')
                    ->label('Profit')
                    ->money('IDR')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('IDR'),
                    ]),
                    
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Cashier')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('print_receipt')
                    ->label('Print')
                    ->url(fn (Sale $record): string => route('admin.sales.print', $record))
                    ->icon('heroicon-m-printer')
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                //
            ]);
    }
    
    public function getSalesData()
    {
        $startDate = Carbon::parse($this->start_date)->startOfDay();
        $endDate = Carbon::parse($this->end_date)->endOfDay();
        
        // Total sales, cost, and profit
        $totals = Sale::query()
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->selectRaw('SUM(total_amount) as total_sales, SUM(cost_amount) as total_cost, SUM(total_amount - cost_amount) as total_profit')
            ->first();
            
        // Total items sold
        $totalItems = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->sum('quantity');
            
        // Total transactions
        $totalTransactions = Sale::query()
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->count();
            
        return [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'total_sales' => $totals->total_sales ?? 0,
            'total_cost' => $totals->total_cost ?? 0,
            'total_profit' => $totals->total_profit ?? 0,
            'total_items' => $totalItems,
            'total_transactions' => $totalTransactions,
            'average_sale' => $totalTransactions > 0 ? ($totals->total_sales / $totalTransactions) : 0,
        ];
    }
    
    public function getViewData(): array
    {
        return [
            'reportData' => $this->getSalesData(),
        ];
    }
    
    public function printReport()
    {
        return redirect()->route('admin.reports.sales.print', [
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
        ]);
    }
}