<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Product, OrderItem};
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

        $favored = false;

        if ($user = $request->user()) {
            // boolval() 函数用于把值转换成bool值
            $favored = boolval($user->favoriteProducts()->find($product->id));
        }

        // 获取商品的评论信息
        $reviews = OrderItem::query()->with(['order.user', 'productSku'])
            ->where('product_id', $product->id)
            ->whereNotNull('reviewed_at')
            ->orderBy('reviewed_at', 'desc')
            ->limit(10)->get();

        return view('products.show', compact('product', 'favored', 'reviews'));
    }

    // 新增收藏入口
    public function favor(Product $product, Request $request)
    {
        $user = $request->user();
        
        // 判断该用户是否已经收藏了该商品
        if ($user->favoriteProducts()->find($product->id)) {
            return [];
        }

        // 如果是新增收藏，attach() 方法可以将当前用户和此商品关联起来
        $user->favoriteProducts()->attach($product);

        return [];
    }

    // 取消收藏
    public function disfavor(Product $product, Request $request)
    {
        $user = $request->user();

        if ($user->favoriteProducts()->find($product->id)) {
            $user->favoriteProducts()->detach($product);
        }

        return [];
    }

    // 用户收藏列表
    public function favorites(Request $request)
    {
        // 获取该用户的收藏商品列表，由于在定义关联关系时已经加了排序规则，这里查询就不需要再次设置了
        $products = $request->user()->favoriteProducts()->paginate(16);

        return view('products.favorites', compact('products'));
    }
}
