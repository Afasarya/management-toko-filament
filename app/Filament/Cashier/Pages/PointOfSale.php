<?php

namespace App\Filament\Cashier\Pages;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Category;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use Carbon\Carbon;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;

class PointOfSale extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Point of Sale';

    protected static ?string $title = 'Point of Sale';
    
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.cashier.pages.point-of-sale';
    
    public array $cart = [];
    public int $cartTotal = 0;
    public string $searchQuery = '';
    public $selectedProduct = null;
    public $quantity = 1;
    public $paymentAmount = 0;
    public $changeAmount = 0;
    public $selectedCategory = null;
    
    // Tambahkan properti-properti form agar dapat diakses langsung
    public $invoice_number;
    public $sale_date;
    public $customer_id;
    public $customer_name = 'Umum';
    
    public function mount()
    {
        $this->invoice_number = $this->generateInvoiceNumber();
        $this->sale_date = now()->format('Y-m-d');
        
        $this->form->fill([
            'invoice_number' => $this->invoice_number,
            'sale_date' => $this->sale_date,
            'customer_id' => null,
            'customer_name' => 'Umum',
        ]);
    }
    
    public function generateInvoiceNumber()
    {
        $lastSale = Sale::orderBy('id', 'desc')->first();
        $lastId = $lastSale ? $lastSale->id : 0;
        $nextId = str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);
        return $nextId;
    }
    
    #[Computed]
    public function products()
    {
        $query = Product::where('stock', '>', 0);
        
        if (!empty($this->searchQuery)) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->searchQuery}%")
                    ->orWhere('code', 'like', "%{$this->searchQuery}%")
                    ->orWhere('sku', 'like', "%{$this->searchQuery}%");
            });
        }
        
        if ($this->selectedCategory) {
            $query->where('category_id', $this->selectedCategory);
        }
        
        return $query->with(['category', 'brand'])
            ->take(10)
            ->get();
    }
    
    #[Computed]
    public function categories()
    {
        return Category::has('products')->get();
    }
    
    public function selectCategory($categoryId)
    {
        $this->selectedCategory = $categoryId == $this->selectedCategory ? null : $categoryId;
    }
    
    public function selectProduct($productId)
    {
        $this->selectedProduct = Product::find($productId);
        $this->quantity = 1;
    }
    
    public function addToCart()
    {
        if (!$this->selectedProduct) {
            return;
        }
        
        if ($this->selectedProduct->stock < $this->quantity) {
            Notification::make()
                ->title('Not enough stock')
                ->body("Only {$this->selectedProduct->stock} items available")
                ->danger()
                ->send();
            return;
        }
        
        // Check if product already in cart
        $existingIndex = null;
        foreach ($this->cart as $index => $item) {
            if ($item['product_id'] === $this->selectedProduct->id) {
                $existingIndex = $index;
                break;
            }
        }
        
        if ($existingIndex !== null) {
            // Update existing item
            $newQuantity = $this->cart[$existingIndex]['quantity'] + $this->quantity;
            
            // Check if we have enough stock
            if ($this->selectedProduct->stock < $newQuantity) {
                Notification::make()
                    ->title('Not enough stock')
                    ->body("Only {$this->selectedProduct->stock} items available")
                    ->danger()
                    ->send();
                return;
            }
            
            $this->cart[$existingIndex]['quantity'] = $newQuantity;
            $this->cart[$existingIndex]['total_price'] = $newQuantity * $this->selectedProduct->selling_price;
            $this->cart[$existingIndex]['total_purchase_price'] = $newQuantity * $this->selectedProduct->purchase_price;
        } else {
            // Add new item
            $this->cart[] = [
                'product_id' => $this->selectedProduct->id,
                'product_name' => $this->selectedProduct->name,
                'price' => $this->selectedProduct->selling_price,
                'purchase_price' => $this->selectedProduct->purchase_price,
                'quantity' => $this->quantity,
                'total_price' => $this->quantity * $this->selectedProduct->selling_price,
                'total_purchase_price' => $this->quantity * $this->selectedProduct->purchase_price,
            ];
        }
        
        $this->updateCartTotal();
        $this->selectedProduct = null;
        $this->quantity = 1;
        
        Notification::make()
            ->title('Product added to cart')
            ->success()
            ->send();
    }
    
    public function removeFromCart($index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
        $this->updateCartTotal();
    }
    
    public function updateCartTotal()
    {
        $this->cartTotal = 0;
        foreach ($this->cart as $item) {
            $this->cartTotal += $item['total_price'];
        }
        
        $this->paymentAmount = $this->cartTotal;
        $this->updateChange();
    }
    
    public function incrementQuantity($index)
    {
        $product = Product::find($this->cart[$index]['product_id']);
        $newQuantity = $this->cart[$index]['quantity'] + 1;
        
        if ($product->stock < $newQuantity) {
            Notification::make()
                ->title('Not enough stock')
                ->body("Only {$product->stock} items available")
                ->danger()
                ->send();
            return;
        }
        
        $this->cart[$index]['quantity'] = $newQuantity;
        $this->cart[$index]['total_price'] = $newQuantity * $this->cart[$index]['price'];
        $this->cart[$index]['total_purchase_price'] = $newQuantity * $this->cart[$index]['purchase_price'];
        $this->updateCartTotal();
    }
    
    public function decrementQuantity($index)
    {
        if ($this->cart[$index]['quantity'] > 1) {
            $this->cart[$index]['quantity'] -= 1;
            $this->cart[$index]['total_price'] = $this->cart[$index]['quantity'] * $this->cart[$index]['price'];
            $this->cart[$index]['total_purchase_price'] = $this->cart[$index]['quantity'] * $this->cart[$index]['purchase_price'];
            $this->updateCartTotal();
        }
    }
    
    public function updatePayment($value)
    {
        $this->paymentAmount = $value;
        $this->updateChange();
    }
    
    public function updateChange()
    {
        $this->changeAmount = max(0, $this->paymentAmount - $this->cartTotal);
    }
    
    public function clearCart()
    {
        $this->cart = [];
        $this->cartTotal = 0;
        $this->paymentAmount = 0;
        $this->changeAmount = 0;
    }
    
    public function quickAmount($amount)
    {
        $this->paymentAmount = $amount;
        $this->updateChange();
    }
    
    public function processSale()
    {
        if (empty($this->cart)) {
            Notification::make()
                ->title('Cart is empty')
                ->body('Please add products to the cart first')
                ->danger()
                ->send();
            return;
        }
        
        if ($this->paymentAmount < $this->cartTotal) {
            Notification::make()
                ->title('Insufficient payment')
                ->body('Payment amount must be equal to or greater than the total')
                ->danger()
                ->send();
            return;
        }
        
        // Begin transaction
        DB::beginTransaction();
        
        try {
            // Create sale - using the public properties directly
            $sale = Sale::create([
                'invoice_number' => $this->invoice_number,
                'sale_date' => $this->sale_date,
                'customer_id' => $this->customer_id,
                'customer_name' => $this->customer_name,
                'total_amount' => $this->cartTotal,
                'payment_amount' => $this->paymentAmount,
                'change_amount' => $this->changeAmount,
                'cost_amount' => collect($this->cart)->sum('total_purchase_price'),
                'user_id' => Auth::id(),
            ]);
            
            // Create sale items
            foreach ($this->cart as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'purchase_price' => $item['purchase_price'],
                    'total_price' => $item['total_price'],
                    'total_purchase_price' => $item['total_purchase_price'],
                ]);
                
                // Update product stock
                $product = Product::find($item['product_id']);
                $product->sold += $item['quantity'];
                $product->stock -= $item['quantity'];
                $product->save();
            }
            
            DB::commit();
            
            // Redirect to print receipt
            return redirect()->route('admin.sales.print', $sale);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Notification::make()
                ->title('Error processing sale')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Grid::make()
                            ->schema([
                                // Gunakan Hidden input untuk invoice_number agar bisa disubmit
                                Hidden::make('invoice_number')
                                    ->required(),
                                    
                                // Tampilkan invoice_number sebagai TextInput yang disabled
                                TextInput::make('invoice_number_display')
                                    ->label('Invoice Number')
                                    ->default(fn() => $this->invoice_number)
                                    ->disabled(),
                                    
                                TextInput::make('sale_date')
                                    ->label('Date')
                                    ->type('date')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn($state) => $this->sale_date = $state),
                            ])->columns(2),
                            
                        Grid::make()
                            ->schema([
                                Select::make('customer_id')
                                    ->label('Customer')
                                    ->options(function() {
                                        return Customer::pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->placeholder('Regular Customer')
                                    ->live()
                                    ->afterStateUpdated(function ($state) {
                                        if ($state) {
                                            $customer = Customer::find($state);
                                            $this->customer_id = $state;
                                            $this->customer_name = $customer ? $customer->name : 'Umum';
                                            $this->form->fill(['customer_name' => $this->customer_name]);
                                        } else {
                                            $this->customer_id = null;
                                            $this->customer_name = 'Umum';
                                            $this->form->fill(['customer_name' => 'Umum']);
                                        }
                                    }),
                                    
                                TextInput::make('customer_name')
                                    ->label('Customer Name')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn($state) => $this->customer_name = $state),
                            ])->columns(2),
                    ]),
            ]);
    }
    
    public function getCompanyName()
    {
        return Setting::getValue('company', 'name', 'GudangX POS');
    }
}