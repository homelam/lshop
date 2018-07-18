<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Tree;
use Encore\Admin\Traits\AdminBuilder;

class Category extends Model
{
    use Tree, AdminBuilder;
    
    // 可填充字段
    protected $fillable = ['name', 'description', 'parent_id', 'is_show', 'sort_order'];

    // 声明字段的类型
    protected $casts = ['is_show' => 'boolean'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setParentColumn('parent_id');
        $this->setOrderColumn('sort_order');
        $this->setTitleColumn('name');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
