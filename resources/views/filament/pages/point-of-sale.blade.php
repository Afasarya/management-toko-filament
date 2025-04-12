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
            
            <!-- Category Selection (Added) -->
            <x-filament::section>
                <h3 class="text-lg font-medium mb-3">Categories</h3>
                <div class="flex flex-wrap gap-2">
                    <button
                        wire:click="selectCategory(null)"
                        class="px-4 py-2 rounded-lg transition-colors duration-200 
                            {{ $selectedCategory === null 
                                ? 'bg-primary-600 text-white dark:bg-primary-500 dark:text-gray-900' 
                                : 'bg-gray-100 hover:bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600' }}"
                    >
                        All
                    </button>
                    
                    @foreach($this->categories as $category)
                        <button
                            wire:key="category-{{ $category->id }}"
                            wire:click="selectCategory({{ $category->id }})"
                            class="px-4 py-2 rounded-lg transition-colors duration-200 
                                {{ $selectedCategory === $category->id 
                                    ? 'bg-primary-600 text-white dark:bg-primary-500 dark:text-gray-900' 
                                    : 'bg-gray-100 hover:bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600' }}"
                        >
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>
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
                                class="block w-full dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:placeholder-gray-400" 
                            />
                        </x-filament::input.wrapper>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @forelse($this->products as $product)
                            <div 
                                wire:key="product-{{ $product->id }}"
                                wire:click="selectProduct({{ $product->id }})"
                                class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 cursor-pointer 
                                    hover:bg-primary-50 dark:hover:bg-primary-950/30 transition-colors 
                                    bg-white dark:bg-gray-800 shadow-sm"
                            >
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ $product->name }}</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $product->code }} | {{ $product->category->name }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-primary-600 dark:text-primary-400">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</p>
                                        <p class="text-sm {{ $product->stock <= $product->min_stock 
                                            ? 'text-warning-600 dark:text-warning-400' 
                                            : 'text-success-600 dark:text-success-400' }}">
                                            Stock: {{ $product->stock }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            @if($searchQuery)
                                <div class="col-span-2 text-center p-4 text-gray-500 dark:text-gray-400">
                                    No products found for "{{ $searchQuery }}"
                                </div>
                            @else
                                <div class="col-span-2 text-center p-4 text-gray-500 dark:text-gray-400">
                                    Select a category or search for products
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
                    
                    <div class="flex flex-col md:flex-row justify-between items-start space-y-4 md:space-y-0
                        bg-primary-50 dark:bg-primary-950/30 p-4 rounded-lg">
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ $selectedProduct->name }}</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $selectedProduct->code }} | {{ $selectedProduct->category->name }}</p>
                            <p class="font-bold text-primary-600 dark:text-primary-400">Rp {{ number_format($selectedProduct->selling_price, 0, ',', '.') }}</p>
                            <p class="text-sm {{ $selectedProduct->stock <= $selectedProduct->min_stock 
                                ? 'text-warning-600 dark:text-warning-400' 
                                : 'text-success-600 dark:text-success-400' }}">
                                Available Stock: {{ $selectedProduct->stock }}
                            </p>
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center">
                                <button
                                    wire:click="$set('quantity', Math.max(1, $wire.quantity - 1))"
                                    class="p-2 bg-gray-200 dark:bg-gray-700 rounded-l-md text-gray-700 dark:text-gray-200 
                                        hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                                >-</button>
                                <input
                                    type="number"
                                    wire:model="quantity"
                                    min="1"
                                    max="{{ $selectedProduct->stock }}"
                                    class="w-16 text-center border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-primary-500 focus:ring-primary-500 shadow-sm rounded-none"
                                />
                                <button
                                    wire:click="$set('quantity', Math.min($wire.selectedProduct.stock, $wire.quantity + 1))"
                                    class="p-2 bg-gray-200 dark:bg-gray-700 rounded-r-md text-gray-700 dark:text-gray-200 
                                        hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                                >+</button>
                            </div>
                            
                            <button
                                wire:click="addToCart"
                                class="px-4 py-2 bg-primary-600 dark:bg-primary-500 text-white dark:text-gray-900 rounded-md 
                                    hover:bg-primary-700 dark:hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500 
                                    focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors"
                            >
                                Add to Cart
                            </button>
                        </div>
                    </div>
                </x-filament::section>
            @endif
            
            <!-- Cart Items -->
            <x-filament::section>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">Cart</h3>
                    
                    @if(count($cart) > 0)
                        <x-filament::button 
                            color="danger" 
                            wire:click="clearCart"
                            size="sm"
                            icon="heroicon-m-trash"
                            icon-only
                        />
                    @endif
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b dark:border-gray-700">
                                <th class="text-left py-2 text-gray-700 dark:text-gray-300">Product</th>
                                <th class="text-right py-2 text-gray-700 dark:text-gray-300">Price</th>
                                <th class="text-center py-2 text-gray-700 dark:text-gray-300">Qty</th>
                                <th class="text-right py-2 text-gray-700 dark:text-gray-300">Subtotal</th>
                                <th class="py-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cart as $index => $item)
                                <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800/70">
                                    <td class="py-2 text-gray-900 dark:text-gray-100">{{ $item['product_name'] }}</td>
                                    <td class="py-2 text-right text-gray-900 dark:text-gray-100">Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                                    <td class="py-2">
                                        <div class="flex items-center justify-center space-x-2">
                                            <button
                                                wire:click="decrementQuantity({{ $index }})"
                                                class="p-1 rounded-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 
                                                    text-gray-600 dark:text-gray-300 focus:outline-none transition-colors"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                                </svg>
                                            </button>
                                            
                                            <span class="text-gray-900 dark:text-gray-100">{{ $item['quantity'] }}</span>
                                            
                                            <button
                                                wire:click="incrementQuantity({{ $index }})"
                                                class="p-1 rounded-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 
                                                    text-gray-600 dark:text-gray-300 focus:outline-none transition-colors"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="py-2 text-right font-medium text-gray-900 dark:text-gray-100">Rp {{ number_format($item['total_price'], 0, ',', '.') }}</td>
                                    <td class="py-2 text-right">
                                        <button
                                            wire:click="removeFromCart({{ $index }})"
                                            class="text-danger-600 dark:text-danger-400 hover:text-danger-900 dark:hover:text-danger-300 transition-colors"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-4 text-center text-gray-500 dark:text-gray-400">
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
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $this->getCompanyName() }}</h2>
                        <p class="text-gray-500 dark:text-gray-400">Point of Sale</p>
                    </div>
                    
                    <div class="px-4 py-3 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                        <h3 class="text-xl font-bold mb-1 text-center text-gray-900 dark:text-gray-100">Total</h3>
                        <p class="text-3xl font-bold text-primary-600 dark:text-primary-400 text-center">
                            Rp {{ number_format($cartTotal, 0, ',', '.') }}
                        </p>
                    </div>
                    
                    <div class="space-y-4">
                        <h3 class="font-medium text-gray-900 dark:text-gray-100">Payment</h3>
                        
                        <div>
                            <label for="payment" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Payment Amount
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400 sm:text-sm">Rp</span>
                                </div>
                                <input 
                                    type="number" 
                                    id="payment" 
                                    wire:model.live="paymentAmount" 
                                    wire:input="setPaymentValue($event.target.value)"
                                    min="{{ $cartTotal }}"
                                    class="focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 pr-12 sm:text-sm 
                                        border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-md text-right" 
                                    placeholder="0"
                                >
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-3 gap-2">
                            <button
                                wire:click="quickAmount({{ $cartTotal }})"
                                class="px-3 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 
                                    rounded-md text-gray-700 dark:text-gray-200 font-medium transition-colors"
                            >
                                Exact
                            </button>
                            
                            <button
                                wire:click="quickAmount({{ round($cartTotal / 1000) * 1000 + 1000 }})"
                                class="px-3 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 
                                    rounded-md text-gray-700 dark:text-gray-200 font-medium transition-colors"
                            >
                                +1K
                            </button>
                            
                            <button
                                wire:click="quickAmount({{ round($cartTotal / 5000) * 5000 + 5000 }})"
                                class="px-3 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 
                                    rounded-md text-gray-700 dark:text-gray-200 font-medium transition-colors"
                            >
                                +5K
                            </button>
                            
                            <button
                                wire:click="quickAmount({{ round($cartTotal / 10000) * 10000 + 10000 }})"
                                class="px-3 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 
                                    rounded-md text-gray-700 dark:text-gray-200 font-medium transition-colors"
                            >
                                +10K
                            </button>
                            
                            <button
                                wire:click="quickAmount({{ round($cartTotal / 50000) * 50000 + 50000 }})"
                                class="px-3 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 
                                    rounded-md text-gray-700 dark:text-gray-200 font-medium transition-colors"
                            >
                                +50K
                            </button>
                            
                            <button
                                wire:click="quickAmount({{ round($cartTotal / 100000) * 100000 + 100000 }})"
                                class="px-3 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 
                                    rounded-md text-gray-700 dark:text-gray-200 font-medium transition-colors"
                            >
                                +100K
                            </button>
                        </div>
                    </div>
                    
                    <div class="px-4 py-3 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                        <h3 class="font-medium mb-1 text-gray-900 dark:text-gray-100">Change</h3>
                        <p class="text-2xl font-bold text-success-600 dark:text-success-400" id="change-amount">
                            Rp {{ number_format($changeAmount, 0, ',', '.') }}
                        </p>
                    </div>
                    
                    <div class="pt-4">
                        <button
                            wire:click="processSale"
                            class="w-full py-4 px-4 bg-primary-600 dark:bg-primary-500 text-white dark:text-gray-900 text-lg font-medium rounded-md 
                                hover:bg-primary-700 dark:hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500 
                                focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors
                                disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
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

    <!-- Script untuk memastikan nilai kembalian diperbarui secara real-time -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            // Listen for payment-updated event
            @this.on('payment-updated', (changeAmount) => {
                document.getElementById('change-amount').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(changeAmount);
            });
            
            // Make sure the change amount updates immediately on input
            const paymentInput = document.getElementById('payment');
            if (paymentInput) {
                paymentInput.addEventListener('input', function() {
                    @this.setPaymentValue(this.value);
                });
            }
        });
    </script>
</x-filament-panels::page>