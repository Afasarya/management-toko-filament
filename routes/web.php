<?php

use App\Http\Controllers\PrintController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Print routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/sales/{sale}/print', [PrintController::class, 'printSaleReceipt'])->name('sales.print');
    Route::get('/purchases/{purchase}/print', [PrintController::class, 'printPurchaseInvoice'])->name('purchases.print');
    Route::get('/reports/sales/print', [PrintController::class, 'printSalesReport'])->name('reports.sales.print');
    Route::get('/reports/inventory/print', [PrintController::class, 'printInventoryReport'])->name('reports.inventory.print');
});

require __DIR__.'/auth.php';