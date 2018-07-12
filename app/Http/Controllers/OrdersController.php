<?php

namespace App\Http\Controllers;

use App\Models\{Order, UserAddress};
use App\Http\Requests\OrderRequest;
use Illuminate\Http\Request;
use App\Services\OrderService;


class OrdersController extends Controller
{
    // 订单列表
    public function index(Request $request)
    {
        $orders = Order::query()->with(['items.product', 'items.productSku'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate();

        return view('orders.index', compact('orders'));
    }

    // 用户下单
    public function store(OrderRequest $request, OrderService $orderService)
    {
        $user = $request->user();
        $address = UserAddress::find($request->input('address_id'));

        $remark = $request->input('remark');
        $items = $request->input('items');

        return $orderService->store($user, $address, $remark, $items);
    }

    // 显示订单详情
    public function show(Order $order, Request $request)
    {
        // 先检查该用户是否有查看该订单的授权
        $this->authorize('own', $order);
        // load()  是在已经查询出来的模型上调用
        return view('orders.show', ['order' => $order->load(['items.productSku', 'items.product'])]);
    }
}
