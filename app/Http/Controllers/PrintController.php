<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;

class PrintController extends Controller
{
    public function printSaleReceipt(Sale $sale)
    {
        $company = [
            'name' => Setting::getValue('company', 'name', 'GudangX'),
            'address' => Setting::getValue('company', 'address', ''),
            'phone' => Setting::getValue('company', 'phone', ''),
            'email' => Setting::getValue('company', 'email', ''),
            'website' => Setting::getValue('company', 'website', ''),
            'logo' => Setting::getValue('company', 'logo', ''),
        ];
        
        $invoice = [
            'signature' => Setting::getValue('invoice', 'signature', ''),
        ];
        
        $pdf = PDF::loadView('prints.sale_receipt', [
            'sale' => $sale,
            'company' => $company,
            'invoice' => $invoice,
        ]);
        
        return $pdf->stream('receipt-' . $sale->invoice_number . '.pdf');
    }
    
    public function printPurchaseInvoice(Purchase $purchase)
    {
        $company = [
            'name' => Setting::getValue('company', 'name', 'GudangX'),
            'address' => Setting::getValue('company', 'address', ''),
            'phone' => Setting::getValue('company', 'phone', ''),
            'email' => Setting::getValue('company', 'email', ''),
            'website' => Setting::getValue('company', 'website', ''),
            'logo' => Setting::getValue('company', 'logo', ''),
        ];
        
        $pdf = PDF::loadView('prints.purchase_invoice', [
            'purchase' => $purchase,
            'company' => $company,
        ]);
        
        return $pdf->stream('purchase-' . $purchase->invoice_number . '.pdf');
    }
    
    public function printSalesReport(Request $request)
    {
        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();
        
        $sales = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->orderBy('sale_date', 'desc')
            ->get();
            
        $totals = [
            'total_sales' => $sales->sum('total_amount'),
            'total_cost' => $sales->sum('cost_amount'),
            'total_profit' => $sales->sum('total_amount') - $sales->sum('cost_amount'),
            'total_items' => $sales->sum(function ($sale) {
                return $sale->items->sum('quantity');
            }),
            'total_transactions' => $sales->count(),
        ];
        
        $company = [
            'name' => Setting::getValue('company', 'name', 'GudangX'),
            'address' => Setting::getValue('company', 'address', ''),
            'phone' => Setting::getValue('company', 'phone', ''),
            'logo' => Setting::getValue('company', 'logo', ''),
        ];
        
        $pdf = PDF::loadView('prints.sales_report', [
            'sales' => $sales,
            'totals' => $totals,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'company' => $company,
            'generated_at' => now(),
        ]);
        
        return $pdf->stream('sales-report-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.pdf');
    }
    
    public function printInventoryReport(Request $request)
    {
        $query = Product::query();
        
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        
        if ($request->brand_id) {
            $query->where('brand_id', $request->brand_id);
        }
        
        if ($request->stock_status === 'in_stock') {
            $query->where('stock', '>', 0);
        } elseif ($request->stock_status === 'low_stock') {
            $query->whereColumn('stock', '<=', 'min_stock')->where('stock', '>', 0);
        } elseif ($request->stock_status === 'out_of_stock') {
            $query->where('stock', '=', 0);
        }
        
        $products = $query->orderBy('name')->get();
        
        $summary = [
            'total_products' => $products->count(),
            'total_items' => $products->sum('stock'),
            'total_value' => $products->sum(function ($product) {
                return $product->stock * $product->purchase_price;
            }),
            'low_stock_products' => $products->filter(function ($product) {
                return $product->stock <= $product->min_stock && $product->stock > 0;
            })->count(),
            'out_of_stock_products' => $products->filter(function ($product) {
                return $product->stock === 0;
            })->count(),
        ];
        
        $filters = [
            'category' => $request->category_id ? Category::find($request->category_id)->name : 'All Categories',
            'brand' => $request->brand_id ? Brand::find($request->brand_id)->name : 'All Brands',
            'stock_status' => match($request->stock_status) {
                'in_stock' => 'In Stock',
                'low_stock' => 'Low Stock',
                'out_of_stock' => 'Out of Stock',
                default => 'All Products',
            },
        ];
        
        $company = [
            'name' => Setting::getValue('company', 'name', 'GudangX'),
            'address' => Setting::getValue('company', 'address', ''),
            'phone' => Setting::getValue('company', 'phone', ''),
            'logo' => Setting::getValue('company', 'logo', ''),
        ];
        
        $pdf = PDF::loadView('prints.inventory_report', [
            'products' => $products,
            'summary' => $summary,
            'filters' => $filters,
            'company' => $company,
            'generated_at' => now(),
        ]);
        
        return $pdf->stream('inventory-report-' . now()->format('Y-m-d') . '.pdf');
    }
}