<?php

namespace App\Filament\Cashier\Widgets;

use App\Models\Sale;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class TodaySales extends BaseWidget
{
    protected function getStats(): array
    {
        $today = now()->format('Y-m-d');
        
        // Get sales for today for the current user
        $salesCount = Sale::where('user_id', Auth::id())
            ->whereDate('sale_date', $today)
            ->count();
            
        $salesTotal = Sale::where('user_id', Auth::id())
            ->whereDate('sale_date', $today)
            ->sum('total_amount');
            
        $salesProfit = Sale::where('user_id', Auth::id())
            ->whereDate('sale_date', $today)
            ->sum('total_amount') - Sale::where('user_id', Auth::id())
            ->whereDate('sale_date', $today)
            ->sum('cost_amount');
            
        // Get all sales for today
        $allSalesTotal = Sale::whereDate('sale_date', $today)
            ->sum('total_amount');
        
        return [
            Stat::make('Your Sales Today', number_format($salesCount))
                ->description('Number of transactions')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary'),
                
            Stat::make('Your Sales Amount', 'Rp ' . number_format($salesTotal, 0, ',', '.'))
                ->description('Total sales value')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
                
            Stat::make('Your Sales Profit', 'Rp ' . number_format($salesProfit, 0, ',', '.'))
                ->description('Total profit generated')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success'),
                
            Stat::make('Total Store Sales', 'Rp ' . number_format($allSalesTotal, 0, ',', '.'))
                ->description('All cashiers combined')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('info'),
        ];
    }
}