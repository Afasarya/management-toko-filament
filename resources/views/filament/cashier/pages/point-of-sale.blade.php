<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Left Side - Product Search & Customer Selection -->
        <div class="md:col-span-2 space-y-6">
            <!-- Customer Information Form -->
            <x-filament::section>
                <h3 class="text-lg font-medium mb-4">Customer Information</h3>
                <form wire:submit="form">
                    {{ $this->form }}
                </form>
            </x-filament::section>
            
            <!-- Category Selection -->
            <x-filament::section>
                <h3 class="text-lg font-medium mb-4">Categories</h3>
                <div class="flex flex-wrap gap-2">
                    <button
                        wire:click="selectCategory(null)"
                        class="px-4 py-2 rounded-lg {{ $selectedCategory === null ? 'bg-primary-500 text-white' : 'bg-gray-100 hover:bg-gray-200' }}"
                    >
                        All
                    </button>
                    
                    @foreach($this->categories as $category)
                        <button
                            wire:key="category-{{ $category->id }}"
                            wire:click="selectCategory({{ $category->id }})"
                            class="px-4 py-2 rounded-lg {{ $selectedCategory === $category->id ? 'bg-primary-500 text-white' : 'bg-gray-100 hover:bg-gray-200' }}"
                        >
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>
            </x-filament::section>
            
            <!-- Product Search -->
            <x-filament::section>
                <div class="space-y-6">
                    <h3 class="text-lg font-medium">Product Search</h3>
                    
                    <div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Search Products</label>
                            <input 
                                type="text"
                                wire:model.live.debounce.300ms="searchQuery"
                                placeholder="Search by product name, code, or SKU..."
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" 
                            />
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        @forelse($this->products as $product)
                            <div 
                                wire:key="product-{{ $product->id }}"
                                wire:click="selectProduct({{ $product->id }})"
                                class="border border-gray-200 rounded-lg p-4 cursor-pointer hover:bg-primary-50 transition"
                            >
                                <div class="flex flex-col h-full">
                                    <h4 class="font-medium text-lg mb-1">{{ $product->name }}</h4>
                                    <p class="text-sm text-gray-500 mb-2">{{ $product->code }}</p>
                                    <div class="flex items-center text-sm text-gray-600 mb-1">
                                        <span class="bg-gray-100 px-2 py-1 rounded">{{ $product->category->name }}</span>
                                    </div>
                                    <div class="mt-auto pt-2 flex justify-between items-center">
                                        <p class="font-bold text-primary-600 text-lg">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</p>
                                        <p class="text-sm px-2 py-1 rounded {{ $product->stock <= $product->min_stock ? 'bg-warning-100 text-warning-800' : 'bg-success-100 text-success-800' }}">
                                            Stock: {{ $product->stock }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-3 text-center p-8 text-gray-500 bg-gray-50 rounded-lg">
                                @if($searchQuery || $selectedCategory)
                                    No products found with the current filters.
                                @else
                                    Select a category or search for products.
                                @endif
                            </div>
                        @endforelse
                    </div>
                </div>
            </x-filament::section>
            
            <!-- Selected Product -->
            @if($selectedProduct)
                <x-filament::section>
                    <h3 class="text-lg font-medium mb-4">Selected Product</h3>
                    
                    <div class="flex flex-col md:flex-row justify-between items-start space-y-4 md:space-y-0 md:space-x-8 p-4 bg-primary-50 rounded-lg">
                        <div>
                            <h4 class="font-medium text-lg mb-1">{{ $selectedProduct->name }}</h4>
                            <p class="text-sm text-gray-500 mb-2">{{ $selectedProduct->code }} | {{ $selectedProduct->category->name }}</p>
                            <p class="font-bold text-primary-600 text-xl mb-1">Rp {{ number_format($selectedProduct->selling_price, 0, ',', '.') }}</p>
                            <p class="text-sm px-2 py-1 inline-block rounded {{ $selectedProduct->stock <= $selectedProduct->min_stock ? 'bg-warning-100 text-warning-800' : 'bg-success-100 text-success-800' }}">
                                Available Stock: {{ $selectedProduct->stock }}
                            </p>
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            <div class="mb-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                                <div class="flex items-center">
                                    <button 
                                        wire:click="$set('quantity', Math.max(1, $wire.quantity - 1))"
                                        class="p-2 bg-gray-200 rounded-l-md text-gray-700 hover:bg-gray-300"
                                    >-</button>
                                    <input 
                                        type="number"
                                        wire:model="quantity"
                                        min="1"
                                        max="{{ $selectedProduct->stock }}"
                                        class="w-20 text-center border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 rounded-none"
                                    />
                                    <button 
                                        wire:click="$set('quantity', Math.min($wire.selectedProduct.stock, $wire.quantity + 1))"
                                        class="p-2 bg-gray-200 rounded-r-md text-gray-700 hover:bg-gray-300"
                                    >+</button>
                                </div>
                            </div>
                            
                            <button 
                                wire:click="addToCart"
                                class="px-6 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                            >
                                Add to Cart
                            </button>
                        </div>
                    </div>
                </x-filament::section>
            @endif
            
            <!-- Cart Items -->
            <x-filament::section>
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium">Shopping Cart</h3>
                    
                    @if(count($cart) > 0)
                        <button
                            wire:click="clearCart"
                            class="px-4 py-1 bg-danger-600 text-white rounded-md text-sm hover:bg-danger-700 focus:outline-none focus:ring-2 focus:ring-danger-500 focus:ring-offset-2"
                        >
                            Clear Cart
                        </button>
                    @endif
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-3 px-4">Product</th>
                                <th class="text-right py-3 px-4">Price</th>
                                <th class="text-center py-3 px-4">Qty</th>
                                <th class="text-right py-3 px-4">Subtotal</th>
                                <th class="py-3 px-4"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cart as $index => $item)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-4 px-4">{{ $item['product_name'] }}</td>
                                    <td class="py-4 px-4 text-right">Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                                    <td class="py-4 px-4">
                                        <div class="flex items-center justify-center space-x-2">
                                            <button
                                                wire:click="decrementQuantity({{ $index }})"
                                                class="p-1 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 focus:outline-none"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                                </svg>
                                            </button>
                                            
                                            <span class="font-medium w-8 text-center">{{ $item['quantity'] }}</span>
                                            
                                            <button
                                                wire:click="incrementQuantity({{ $index }})"
                                                class="p-1 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 focus:outline-none"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-right font-medium">Rp {{ number_format($item['total_price'], 0, ',', '.') }}</td>
                                    <td class="py-4 px-4 text-right">
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
                                    <td colspan="5" class="py-8 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                            <p>The cart is empty. Add some products!</p>
                                        </div>
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
                <div class="space-y-8">
                    <div class="text-center">
                        <h2 class="text-2xl font-bold mb-1">{{ $this->getCompanyName() }}</h2>
                        <p class="text-gray-500">Point of Sale</p>
                    </div>
                    
                    <div class="p-6 bg-gray-100 rounded-lg">
                        <h3 class="text-xl font-bold mb-2 text-center">Total</h3>
                        <p class="text-3xl font-bold text-primary-600 text-center">
                            Rp {{ number_format($cartTotal, 0, ',', '.') }}
                        </p>
                    </div>
                    
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium">Payment</h3>
                        
                        <div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Amount</label>
                                <input 
                                    type="number"
                                    wire:model.live="paymentAmount"
                                    class="block w-full text-right rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 text-xl py-3"
                                    min="{{ $cartTotal }}"
                                />
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-3 gap-3">
                            <button
                                wire:click="quickAmount({{ $cartTotal }})"
                                class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-md text-gray-700 font-medium"
                            >
                                Exact
                            </button>
                            
                            <button
                                wire:click="quickAmount({{ round($cartTotal / 1000) * 1000 + 1000 }})"
                                class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-md text-gray-700 font-medium"
                            >
                                +1K
                            </button>
                            
                            <button
                                wire:click="quickAmount({{ round($cartTotal / 5000) * 5000 + 5000 }})"
                                class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-md text-gray-700 font-medium"
                            >
                                +5K
                            </button>
                            
                            <button
                                wire:click="quickAmount({{ round($cartTotal / 10000) * 10000 + 10000 }})"
                                class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-md text-gray-700 font-medium"
                            >
                                +10K
                            </button>
                            
                            <button
                                wire:click="quickAmount({{ round($cartTotal / 50000) * 50000 + 50000 }})"
                                class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-md text-gray-700 font-medium"
                            >
                                +50K
                            </button>
                            
                            <button
                                wire:click="quickAmount({{ round($cartTotal / 100000) * 100000 + 100000 }})"
                                class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-md text-gray-700 font-medium"
                            >
                                +100K
                            </button>
                        </div>
                    </div>
                    
                    <div class="p-6 bg-gray-100 rounded-lg">
                        <h3 class="font-medium mb-2">Change</h3>
                        <p class="text-2xl font-bold text-success-600">
                            Rp {{ number_format($changeAmount, 0, ',', '.') }}
                        </p>
                    </div>
                    
                    <div class="pt-4">
                        <button
                            wire:click="processSale"
                            class="w-full py-4 bg-primary-600 text-white text-lg font-medium rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
                            {{ empty($cart) || $paymentAmount < $cartTotal ? 'disabled' : '' }}
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Complete Sale
                        </button>
                    </div>
                </div>
            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>