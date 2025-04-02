<x-filament-panels::page>
    <div class="space-y-6">
        <form wire:submit="table">
            {{ $this->form }}
        </form>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-filament::section>
                <div class="flex flex-col items-center justify-center space-y-2">
                    <span class="text-sm font-medium">Total Sales</span>
                    <span class="text-2xl font-bold text-primary-600">
                        Rp {{ number_format($reportData['total_sales'], 0, ',', '.') }}
                    </span>
                </div>
            </x-filament::section>
            
            <x-filament::section>
                <div class="flex flex-col items-center justify-center space-y-2">
                    <span class="text-sm font-medium">Total Profit</span>
                    <span class="text-2xl font-bold text-success-600">
                        Rp {{ number_format($reportData['total_profit'], 0, ',', '.') }}
                    </span>
                </div>
            </x-filament::section>
            
            <x-filament::section>
                <div class="flex flex-col items-center justify-center space-y-2">
                    <span class="text-sm font-medium">Total Transactions</span>
                    <span class="text-2xl font-bold text-gray-600">
                        {{ number_format($reportData['total_transactions'], 0, ',', '.') }}
                    </span>
                </div>
            </x-filament::section>
        </div>
        
        <div class="flex justify-end">
            <x-filament::button wire:click="printReport" tag="a" target="_blank" color="gray">
                <x-filament::icon
                    alias="heroicon-m-printer"
                    class="mr-2 h-5 w-5"
                />
                Print Report
            </x-filament::button>
        </div>
        
        {{ $this->table }}
    </div>
</x-filament-panels::page>