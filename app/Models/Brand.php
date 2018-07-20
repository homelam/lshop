<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = ['name', 'logo', 'description', 'site_url', 'sort_order', 'is_show'];

    protected $casts = [
        'is_show' => 'boolean',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // LOGO
    public function getImageUrlAttribute()
    {
        // 如果 logo 字段本身就已经是完整的 url 就直接返回
        if (Str::startsWith($this->attributes['logo'], ['http://', 'https://'])) {
            return $this->attributes['logo'];
        }

        return \Storage::disk('public')->url($this->attributes['logo']);
    }
}
