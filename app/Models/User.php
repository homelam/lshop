<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'mobile', 'email_verified', 'birthday', 'last_login_ip', 'last_login_at',
        'social_id', 'nickname', 'avatar', 'provider'
    ];

    protected $casts = [
        'email_verified' => 'boolean',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public static $verifiedColors = [
        'YES'    => 'green',
        'NO'   => 'red',
    ];

    // 用户和收货地址是一对多的关系
    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    public function favoriteProducts()
    {
        // 关联模型的时候希望查找出来的数据是按照时间排序 withTimestamps() 表示中间表带有时间戳字段
        return $this->belongsToMany(Product::class, 'user_favorite_products')
            ->withTimestamps()
            ->orderBy('user_favorite_products.created_at', 'desc');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }
}
