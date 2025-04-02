<?php

namespace App\Filament\Cashier\Pages;

use App\Models\Sale;
use Carbon\Carbon;
use Filament\Pages\Dashboard as BasePage;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BasePage
{
    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Cashier\Widgets\TodaySales::class,
        ];
    }
    
    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Cashier\Widgets\RecentSales::class,
        ];
    }
    
    public function getColumns(): int|array    {
        return 2;
    }
}