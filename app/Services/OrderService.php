<?php

namespace App\Services;

use App\Models\{User, UserAddress, Order, ProductSku};
use App\Exceptions\InvalidRequestException;
use App\Jobs\CloseOrder;
use Carbon\Carbon;

class OrderService
{
    // 保存订单
    public function store(User $user, UserAddress $address, $remark, $items)
    {
        // 涉及金额，开启数据库事务, 任一环节出错，事务将会回滚
        $order = \DB::transaction(function() use ($user, $address, $remark, $items) {
            // 更新地址最后使用的时间
            $address->update(['last_used_at' => Carbon::now()]);
            // 创建一个订单
            $order = new Order([
                'address' => [
                    'address' => $address->full_address,
                    'zip' => $address->zip,
                    'contact_name' => $address->contact_name,
                    'contact_phone' => $address->contact_phone,
                ],
                'remark' => $remark,
                'total_amount' => 0,
            ]);
            // 订单关联到用户
            $order->user()->associate($user);
            $order->save();

            $totalAmount = 0; // 初始化订单金额为0， 在一下步骤中进行计算

            // 遍历订单的商品数量
            foreach ($items as $data) {
                $sku = ProductSku::find($data['sku_id']);
                // 创建一个OrderItem 并直接与当前订单关联
                $item = $order->items()->make([
                    'amount' => $data['amount'],
                    'price' => $sku->price,
                ]);
                $item->product()->associate($sku->product_id);
                $item->productSku()->associate($sku);
                $item->save();
                $totalAmount += $sku->price * $data['amount'];
                
                // 减少对应商品的库存
                if ($sku->decreaseStock($data['amount']) <= 0) {
                    throw new InvalidRequestException('该商品库存不足');
                }
            }

            // 更新订单总金额
            $order->update(['total_amount' => $totalAmount]);

            // 将下单的商品从购物车中移除
            $skuIds = collect($items)->pluck('sku_id')->all();
            app(CartService::class)->remove($skuIds);

            return $order;
        });

        // 触发任务
        dispatch(new CloseOrder($order, config('app.order_ttl')));
        return $order;
    }
}