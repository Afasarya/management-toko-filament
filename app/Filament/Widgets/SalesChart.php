<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SalesChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly Sales & Profit';
    
    protected static ?int $sort = 2;
    
    protected function getData(): array
    {
        $data = $this->getSalesData();
        
        return [
            'datasets' => [
                [
                    'label' => 'Sales',
                    'data' => $data['sales'],
                    'backgroundColor' => 'rgba(249, 115, 22, 0.2)',
                    'borderColor' => 'rgb(249, 115, 22)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Profit',
                    'data' => $data['profit'],
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $data['labels'],
        ];
    }
    
    protected function getSalesData(): array
    {
        $salesData = DB::table('sales')
            ->select(
                DB::raw('MONTH(sale_date) as month'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('SUM(total_amount - cost_amount) as total_profit')
            )
            ->whereYear('sale_date', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        $labels = [];
        $sales = [];
        $profit = [];
        
        // Initialize with zeros for all months
        for ($i = 1; $i <= 12; $i++) {
            $monthName = Carbon::create(null, $i, 1)->format('M');
            $labels[] = $monthName;
            $sales[$i-1] = 0;
            $profit[$i-1] = 0;
        }
        
        // Fill in the actual data
        foreach ($salesData as $data) {
            $month = $data->month;
            $sales[$month-1] = (float) $data->total_sales;
            $profit[$month-1] = (float) $data->total_profit;
        }
        
        return [
            'labels' => $labels,
            'sales' => $sales,
            'profit' => $profit,
        ];
    }
    
    protected function getType(): string
    {
        return 'bar';
    }
}