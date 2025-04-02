<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Left Side - Product Search & Customer Selection -->
        <div class="md:col-span-2">
            <!-- Customer Information Form -->
            <x-filament::section>
                <form wire:submit="form">
                    {{ $this->form }}
                </form>
            </x-filament::section>
            
            <!-- Product Search -->
            <x-filament::section>
                <div class="space-y-4">
                    <h3 class="text-lg font-medium">Product Search</h3>
                    
                    <div>
                        <x-filament::input.wrapper>
                            <x-filament::input 
                                type="text"
                                wire:model.live.debounce.300ms="searchQuery"
                                placeholder="Search by product name, code, or SKU..."
                                class="block w-full" 
                            />
                        </x-filament::input.wrapper>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @forelse($this->products as $product)
                            <div 
                                wire:key="product-{{ $product->id }}"
                                wire:click="selectProduct({{ $product->id }})"
                                class="border border-gray-200 rounded-lg p-3 cursor-pointer hover:bg-primary-50 transition"
                            >
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-medium">{{ $product->name }}</h4>
                                        <p class="text-sm text-gray-500">{{ $product->code }} | {{ $product->category->name }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-primary-600">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</p>
                                        <p class="text-sm {{ $product->stock <= $product->min_stock ? 'text-warning-600' : 'text-success-600' }}">Stock: {{ $product->stock }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            @if($searchQuery)
                                <div class="col-span-2 text-center p-4 text-gray-500">
                                    No products found for "{{ $searchQuery }}"
                                </div>
                            @endif
                        @endforelse
                    </div>
                </div>
            </x-filament::section>
            
            <!-- Selected Product -->
            @if($selectedProduct)
                <x-filament::section>
                    <h3 class="text-lg font-medium mb-4">Selected Product</h3>
                    
                    <div class="flex flex-col md:flex-row justify-between items-start space-y-4 md:space-y-0">
                        <div>
                            <h4 class="font-medium">{{ $selectedProduct->name }}</h4>
                            <p class="text-sm text-gray-500">{{ $selectedProduct->code }} | {{ $selectedProduct->category->name }}</p>
                            <p class="font-bold text-primary-600">Rp {{ number_format($selectedProduct->selling_price, 0, ',', '.') }}</p>
                            <p class="text-sm {{ $selectedProduct->stock <= $selectedProduct->min_stock ? 'text-warning-600' : 'text-success-600' }}">
                                Available Stock: {{ $selectedProduct->stock }}
                            </p>
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            <x-filament::input.wrapper>
                                <x-filament::input.label>
                                    Quantity
                                </x-filament::input.label>
                                <x-filament::input
                                    type="number"
                                    wire:model="quantity"
                                    min="1"
                                    max="{{ $selectedProduct->stock }}"
                                    class="w-24 text-center"
                                />
                            </x-filament::input.wrapper>
                            
                            <x-filament::button wire:click="addToCart">
                                Add to Cart
                            </x-filament::button>
                        </div>
                    </div>
                </x-filament::section>
            @endif
            
            <!-- Cart Items -->
            <x-filament::section>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">Cart</h3>
                    
                    <x-filament::button 
                        color="danger" 
                        wire:click="clearCart"
                        size="sm"
                        icon="heroicon-m-trash"
                        icon-only
                    />
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2">Product</th>
                                <th class="text-right py-2">Price</th>
                                <th class="text-center py-2">Qty</th>
                                <th class="text-right py-2">Subtotal</th>
                                <th class="py-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cart as $index => $item)
                                <tr class="border-b">
                                    <td class="py-2">{{ $item['product_name'] }}</td>
                                    <td class="py-2 text-right">Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                                    <td class="py-2">
                                        <div class="flex items-center justify-center space-x-2">
                                            <button
                                                wire:click="decrementQuantity({{ $index }})"
                                                class="p-1 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 focus:outline-none"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                                </svg>
                                            </button>
                                            
                                            <span>{{ $item['quantity'] }}</span>
                                            
                                            <button
                                                wire:click="incrementQuantity({{ $index }})"
                                                class="p-1 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 focus:outline-none"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="py-2 text-right">Rp {{ number_format($item['total_price'], 0, ',', '.') }}</td>
                                    <td class="py-2 text-right">
                                        <button
                                            wire:click="removeFromCart({{ $index }})"
                                            class="text-danger-600 hover:text-danger-900"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-4 text-center text-gray-500">
                                        The cart is empty. Add some products!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        </div>
        
        <!-- Right Side - Payment Processing -->
        <div>
            <x-filament::section class="sticky top-4">
                <div class="space-y-6">
                    <div class="text-center">
                        <h2 class="text-2xl font-bold">{{ $this->getCompanyName() }}</h2>
                        <p class="text-gray-500">Point of Sale</p>
                    </div>
                    
                    <div class="px-4 py-3 bg-gray-100 rounded-lg">
                        <h3 class="text-xl font-bold mb-1 text-center">Total</h3>
                        <p class="text-3xl font-bold text-primary-600 text-center">
                            Rp {{ number_format($cartTotal, 0, ',', '.') }}
                        </p>
                    </div>
                    
                    <div class="space-y-4">
                        <h3 class="font-medium">Payment</h3>
                        
                        <div>
                            <x-filament::input.wrapper>
                                <x-filament::input.label>
                                    Amount
                                </x-filament::input.label>
                                <x-filament::input
                                    type="number"
                                    wire:model.live="paymentAmount"
                                    class="text-right"
                                    min="{{ $cartTotal }}"
                                />
                            </x-filament::input.wrapper>
                        </div>
                        
                        <div class="grid grid-cols-3 gap-2">
                            <x-filament::button
                                wire:click="quickAmount({{ $cartTotal }})"
                                color="gray"
                                size="sm"
                            >
                                Exact
                            </x-filament::button>
                            
                            <x-filament::button
                                wire:click="quickAmount({{ round($cartTotal / 1000) * 1000 + 1000 }})"
                                color="gray"
                                size="sm"
                            >
                                +1K
                            </x-filament::button>
                            
                            <x-filament::button
                                wire:click="quickAmount({{ round($cartTotal / 5000) * 5000 + 5000 }})"
                                color="gray"
                                size="sm"
                            >
                                +5K
                            </x-filament::button>
                            
                            <x-filament::button
                                wire:click="quickAmount({{ round($cartTotal / 10000) * 10000 + 10000 }})"
                                color="gray"
                                size="sm"
                            >
                                +10K
                            </x-filament::button>
                            
                            <x-filament::button
                                wire:click="quickAmount({{ round($cartTotal / 50000) * 50000 + 50000 }})"
                                color="gray"
                                size="sm"
                            >
                                +50K
                            </x-filament::button>
                            
                            <x-filament::button
                                wire:click="quickAmount({{ round($cartTotal / 100000) * 100000 + 100000 }})"
                                color="gray"
                                size="sm"
                            >
                                +100K
                            </x-filament::button>
                        </div>
                    </div>
                    
                    <div class="px-4 py-3 bg-gray-100 rounded-lg">
                        <h3 class="font-medium mb-1">Change</h3>
                        <p class="text-2xl font-bold text-success-600">
                            Rp {{ number_format($changeAmount, 0, ',', '.') }}
                        </p>
                    </div>
                    
                    <div class="pt-4">
                        <x-filament::button
                            wire:click="processSale"
                            class="w-full justify-center py-3"
                            size="lg"
                            :disabled="empty($cart) || $paymentAmount < $cartTotal"
                        >
                            Complete Sale
                        </x-filament::button>
                    </div>
                </div>
            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>