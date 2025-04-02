<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'sale_date',
        'total_amount',
        'payment_amount',
        'change_amount',
        'cost_amount',
        'customer_id',
        'customer_name',
        'user_id',
    ];

    protected $casts = [
        'sale_date' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
    
    public function getItemCountAttribute(): int
    {
        return $this->items()->sum('quantity');
    }
    
    public function getProfitAttribute(): int
    {
        return $this->total_amount - $this->cost_amount;
    }
    
    public function getProfitPercentageAttribute(): float
    {
        if ($this->cost_amount > 0) {
            return round(($this->profit / $this->cost_amount) * 100, 2);
        }
        return 0;
    }
}