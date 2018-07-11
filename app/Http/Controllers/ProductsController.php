<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Exceptions\InvalidRequestException;

class ProductsController extends Controller
{
    //
    public function index(Request $request)
    {   
        // 创建一个查询构造器
        $builder = Product::query()->where('on_sale', true);
        
        // 判断是否有提交 search 参数，如果有就赋值给 $search 变量
        // search 参数用来模糊搜索商品
        if ($search = $request->input('search', '')) {
            $like = '%'.$search.'%';
            // 模糊收索商品名称，商品详情，SKU，标题，SKU描述
            $builder->where(function($query) use ($like) {
                $query->where('name', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhereHas('skus', function($query) use ($like) {
                        $query->where('name', 'like', $like)
                            ->orWhere('description', 'like', $like);
                    });
            });
        }

        // 是否有提交排序 参数
        if ($order = $request->input('order', '')) {
            // 是否是以 _asc 或者 _desc结尾
            if (preg_match('/^(.+)_(asc|desc)/', $order, $m)) {
                if (in_array($m[1], ['price', 'sold_count', 'rating'])) {
                    $builder->orderBy($m[1], $m[2]);
                }
            }
        }
        
        $products = $builder->paginate(16);

        return view('products.index', [
            'products' => $products,
            'filters' => [
                'search' => $search,
                'order' => $order,
            ],
        ]);
    }

    public function show(Product $product, Request $request)
    {
        // 如果该商品不是在上架的，则抛出异常
        if (!$product->on_sale) {
            throw new InvalidRequestException('该商品未上架');
        }

        return view('products.show', compact('product'));
    }
}
