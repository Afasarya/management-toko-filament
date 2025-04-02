<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'group',
        'key',
        'value',
    ];
    
    public static function getValue(string $group, string $key, mixed $default = null): mixed
    {
        $setting = self::where('group', $group)
            ->where('key', $key)
            ->first();
            
        return $setting ? $setting->value : $default;
    }
    
    public static function setValue(string $group, string $key, mixed $value): void
    {
        self::updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => $value]
        );
    }
}