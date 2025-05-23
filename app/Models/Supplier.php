<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'address',
        'phone',
        'registration_date',
    ];

    protected $casts = [
        'registration_date' => 'date',
    ];

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }
    
    public function getTotalPurchasesAttribute(): int
    {
        return $this->purchases()->count();
    }
    
    public function getTotalSpentAttribute(): int
    {
        return $this->purchases()->sum('total_amount');
    }
}