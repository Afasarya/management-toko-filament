<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected function getStats(): array
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        
        // Today's sales
        $todaySales = Sale::whereDate('sale_date', $today)->sum('total_amount');
        $yesterdaySales = Sale::whereDate('sale_date', $yesterday)->sum('total_amount');
        $salesChange = $yesterdaySales > 0 
            ? round((($todaySales - $yesterdaySales) / $yesterdaySales) * 100, 2)
            : 100;
        
        // This month's sales
        $thisMonthSales = Sale::whereDate('sale_date', '>=', $thisMonth)->sum('total_amount');
        $lastMonthSales = Sale::whereDate('sale_date', '>=', $lastMonth)
            ->whereDate('sale_date', '<', $thisMonth)
            ->sum('total_amount');
        $monthSalesChange = $lastMonthSales > 0 
            ? round((($thisMonthSales - $lastMonthSales) / $lastMonthSales) * 100, 2)
            : 100;
            
        // Today's profit
        $todayProfit = Sale::whereDate('sale_date', $today)->sum('total_amount') - Sale::whereDate('sale_date', $today)->sum('cost_amount');
        $yesterdayProfit = Sale::whereDate('sale_date', $yesterday)->sum('total_amount') - Sale::whereDate('sale_date', $yesterday)->sum('cost_amount');
        $profitChange = $yesterdayProfit > 0 
            ? round((($todayProfit - $yesterdayProfit) / $yesterdayProfit) * 100, 2)
            : 100;
            
        // Low stock products
        $lowStockProducts = Product::whereColumn('stock', '<=', 'min_stock')->count();
        $totalProducts = Product::count();
        $lowStockPercentage = $totalProducts > 0 
            ? round(($lowStockProducts / $totalProducts) * 100, 2)
            : 0;
        
        return [
            Stat::make('Today\'s Sales', 'Rp ' . number_format($todaySales, 0, ',', '.'))
                ->description($salesChange >= 0 ? "{$salesChange}% increase" : abs($salesChange) . '% decrease')
                ->descriptionIcon($salesChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($salesChange >= 0 ? 'success' : 'danger')
                ->chart([7, 2, 10, 3, 15, 4, $todaySales / 1000]),
                
            Stat::make('Monthly Sales', 'Rp ' . number_format($thisMonthSales, 0, ',', '.'))
                ->description($monthSalesChange >= 0 ? "{$monthSalesChange}% increase" : abs($monthSalesChange) . '% decrease')
                ->descriptionIcon($monthSalesChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($monthSalesChange >= 0 ? 'success' : 'danger'),
                
            Stat::make('Today\'s Profit', 'Rp ' . number_format($todayProfit, 0, ',', '.'))
                ->description($profitChange >= 0 ? "{$profitChange}% increase" : abs($profitChange) . '% decrease')
                ->descriptionIcon($profitChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($profitChange >= 0 ? 'success' : 'danger'),
                
            Stat::make('Low Stock Products', $lowStockProducts)
                ->description("{$lowStockPercentage}% of total products")
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('warning'),
                
            Stat::make('Total Customers', Customer::count())
                ->icon('heroicon-m-users'),
                
            Stat::make('Total Suppliers', Supplier::count())
                ->icon('heroicon-m-building-storefront'),
        ];
    }
}