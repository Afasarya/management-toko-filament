<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
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

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
    
    public function getTotalSalesAttribute(): int
    {
        return $this->sales()->count();
    }
    
    public function getTotalSpentAttribute(): int
    {
        return $this->sales()->sum('total_amount');
    }
}