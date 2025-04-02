<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'sku',
        'name',
        'purchase_price',
        'selling_price',
        'description',
        'category_id',
        'brand_id',
        'sold',
        'purchased',
        'stock',
        'min_stock',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
    
    public function getProfit(): int
    {
        return $this->selling_price - $this->purchase_price;
    }
    
    public function getProfitPercentage(): float
    {
        if ($this->purchase_price > 0) {
            return round(($this->getProfit() / $this->purchase_price) * 100, 2);
        }
        return 0;
    }
    
    public function isLowStock(): bool
    {
        return $this->stock <= $this->min_stock;
    }
    
    public function getTotalValue(): int
    {
        return $this->stock * $this->purchase_price;
    }
}