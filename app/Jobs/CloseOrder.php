<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Order;

// 当创建订单之后一定时间内没有支付，将关闭订单并退回减去的库存
class CloseOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order, $delay)
    {
        $this->order = $order;
        // 设置延时时间，delay() 方法的参数代表多少秒后执行
        $this->delay($delay);
    }

    /**
     * Execute the job. 当队列处理器从队列中去除任务时，会调用handle() 方法
     *
     * @return void
     */
    public function handle()
    {
        // 判断对应的订单是否已经被支付，如果已经支付则不需要关闭订单，直接退出
        if ($this->order->paid_at) {
            return;
        }

        // 通过事务执行sql
        \DB::transaction(function() {
            // 将订单的closed修改为true
            $this->order->update(['closed' => true]);

            if ($this->order->couponCode) {
                $this->order->couponCode->changeUsed(false);
            }

            // 循环归还商品库存
            foreach($this->order->items as $item) {
                $item->productSku->addStock($item->amount);
            }
        });
    }
}
