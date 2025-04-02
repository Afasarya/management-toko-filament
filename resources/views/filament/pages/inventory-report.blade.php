<x-filament-panels::page>
    <div class="space-y-6">
        <form wire:submit="table">
            {{ $this->form }}
        </form>
        
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <x-filament::section>
                <div class="flex flex-col items-center justify-center space-y-2">
                    <span class="text-sm font-medium">Total Products</span>
                    <span class="text-xl font-bold text-primary-600">
                        {{ number_format($inventorySummary['total_products'], 0, ',', '.') }}
                    </span>
                </div>
            </x-filament::section>
            
            <x-filament::section>
                <div class="flex flex-col items-center justify-center space-y-2">
                    <span class="text-sm font-medium">Total Items</span>
                    <span class="text-xl font-bold text-primary-600">
                        {{ number_format($inventorySummary['total_items'], 0, ',', '.') }}
                    </span>
                </div>
            </x-filament::section>
            
            <x-filament::section>
                <div class="flex flex-col items-center justify-center space-y-2">
                    <span class="text-sm font-medium">Inventory Value</span>
                    <span class="text-xl font-bold text-success-600">
                        Rp {{ number_format($inventorySummary['total_value'], 0, ',', '.') }}
                    </span>
                </div>
            </x-filament::section>
            
            <x-filament::section>
                <div class="flex flex-col items-center justify-center space-y-2">
                    <span class="text-sm font-medium">Low Stock</span>
                    <span class="text-xl font-bold text-warning-600">
                        {{ number_format($inventorySummary['low_stock_products'], 0, ',', '.') }}
                    </span>
                </div>
            </x-filament::section>
            
            <x-filament::section>
                <div class="flex flex-col items-center justify-center space-y-2">
                    <span class="text-sm font-medium">Out of Stock</span>
                    <span class="text-xl font-bold text-danger-600">
                        {{ number_format($inventorySummary['out_of_stock_products'], 0, ',', '.') }}
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