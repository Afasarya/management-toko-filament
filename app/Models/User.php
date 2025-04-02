<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'address',
        'phone',
        'birth_date',
        'active_date',
        'avatar',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'birth_date' => 'date',
        'active_date' => 'date',
    ];
    
    /**
     * Determine if the user can access the given Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->role === 'admin';
        }
        
        if ($panel->getId() === 'cashier') {
            return in_array($this->role, ['admin', 'kasir']);
        }
        
        return false;
    }
    
    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
    
    /**
     * Check if user is cashier
     */
    public function isCashier(): bool
    {
        return $this->role === 'kasir';
    }
    
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
    
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
    
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
    
    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }
}