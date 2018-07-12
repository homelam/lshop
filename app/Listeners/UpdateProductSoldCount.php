<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\OrderItem;

class UpdateProductSoldCount implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  OrderPaid  $event
     * @return void
     */
    public function handle(OrderPaid $event)
    {
        // 从事件对象取出对应的订单
        $order = $event->getOrder();
        // 循环遍历订单商品
        foreach ($order->items as $item) {
            $product = $item->product;
            // 计算对应商品的销量
            $soldCount = OrderItem::query()
                ->where('product_id', $product->id)
                ->whereHas('order', function($query) {
                    $query->whereNotNull('paid_at');
                })->sum('amount');
            
            // 更新商品销量
            $product->update(['sold_count' => $soldCount]);
        }
    }
}
