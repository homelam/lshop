<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AddCartRequest;
use App\Models\CartItem;
use App\Models\ProductSku;

class CartController extends Controller
{
    // 查看购物车
    public function index(Request $request)
    {
        // with 预加载
        $cartItems = $request->user()->cartItems()->with(['productSku.product'])->get();
        // 获取用户收货地址
        $addresses = $request->user()->addresses()->orderBy('last_used_at', 'desc')->get();

        return view('cart.index', compact('cartItems', 'addresses'));
    }

    public function add(AddCartRequest $request)
    {
        $user = $request->user();

        $skuId = $request->input('sku_id');
        $amount = $request->input('amount');

        // 从数据库中查询该商品是否存在与购物车中
        if ($cart = $user->cartItems()->where('product_sku_id', $skuId)->first()) {
            // 如果存在则直接叠加商品数量
            $cart->update(['amount' => $cart->amount + $amount]);
        } else {
            // 创建一个新的购物车纪录
            $cart = new CartItem(['amount' => $amount]);
            $cart->user()->associate($user);
            $cart->productSku()->associate($skuId);
            $cart->save();
        }

        return [];
    }

    // 购物车移除商品功能
    public function remove(ProductSku $sku, Request $request)
    {
        $request->user()->cartItems()->where('product_sku_id', $sku->id)->delete();

        return [];
    }
}
