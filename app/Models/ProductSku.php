<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Exceptions\InternalException;

class ProductSku extends Model
{
    //
    protected $fillable = ['sku', 'description', 'price', 'stock', 'picture'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // 减少库存
    public function decreaseStock(int $amount)
    {
        if ($amount < 0) {
            throw new InternalException('减少库存不能小于0');
        }

        // $this->newQuery() 方法来获取数据库的查询构造器 ORM查询构造器的写操作只会返回true 或者 false
        return $this->newQuery()->where('id', $this->id)->where('stock', '>=', $amount)->decrement('stock', $amount);
    }

    // 增加库存
    public function addStock(int $amount)
    {
        if ($amount < 0) {
            throw new InternalException('增加库存不能小于0');
        }

        $this->increment('stock', $amount);
    }
}
