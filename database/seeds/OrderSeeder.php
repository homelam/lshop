<?php

use Illuminate\Database\Seeder;
use App\Models\{Order, OrderItem, Product};

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = app(Faker\Generator::class);
        $orders = factory(Order::class, 50)->create();  // 创建50笔订单

        $products = collect([]); // 被购买的商品，收集用户更新商品销量和评分

        foreach ($orders as $order) {
            // 每笔订单抽取1-3个商品
            $item = factory(OrderItem::class, random_int(1, 3))->create([
                'order_id' => $order->id,
                'rating' => $order->reviewed ? random_int(1, 5) : null,
                'review' => $order->reviewed ? $faker->sentence : null,
                'reviewed_at' => $order->reviewed ? $faker->dateTimeBetween($order->paid_at) : null,
            ]);

            // 计算订单总价
            $total_amount = $item->sum(function(OrderItem $item) {
                return $item->price * $item->amount;
            });

            // 如果有优惠券，计算优惠后的价格
            if ($order->couponCode) {
                $total_amount = $order->couponCode->getAdjustedPrice($total_amount);
            }

            // 更新订单总价
            $order->update(['total_amount' => $total_amount]);
            
            // 将订单的商品加入商品集合中
            $products = $products->merge($item->pluck('product'));
        }

        $products->unique('id')->each(function(Product $product) {
            $result = OrderItem::query()->where('product_id', $product->id)
                ->whereHas('order', function($query) {
                    $query->whereNotNull('paid_at');
                })->first([
                    \DB::raw('count(*) as review_count'),
                    \DB::raw('avg(rating) as rating'),
                    \DB::raw('sum(amount) as sold_count')
                ]);
            
            $product->update([
                'rating' => $result->rating ?: 5,
                'review_count' => $result->review_count,
                'sold_count' => $result->sold_count,
            ]);
        });
    }
}
