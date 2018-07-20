<?php

namespace App\Http\Controllers;

use App\Models\{Order, UserAddress, CouponCode};
use App\Http\Requests\{OrderRequest, ApplyRefundRequest};
use Illuminate\Http\Request;
use App\Services\OrderService;
use Carbon\Carbon;
use App\Http\Requests\SendReviewRequest;
use App\Exceptions\{InvalidRequestException, CouponCodeUnavailableException};
use App\Events\OrderReviewed;


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

        $remark = $request->input('remark'); // 订单备注
        $items = $request->input('items');
        $coupon = null;

        // 如果用户提交了优惠码
        if ($code = $request->input('coupon_code')) {
            $coupon = CouponCode::where('code', $code)->first();
            if (!$coupon) {
                throw new CouponCodeUnavailableException('优惠券不存在');
            }
        }

        return $orderService->store($user, $address, $remark, $items, $coupon);
    }

    // 显示订单详情
    public function show(Order $order, Request $request)
    {
        // 先检查该用户是否有查看该订单的授权
        $this->authorize('own', $order);
        // load()  是在已经查询出来的模型上调用
        return view('orders.show', ['order' => $order->load(['items.productSku', 'items.product'])]);
    }

    // 用户确认收货
    public function received(Order $order, Request $request)
    {
        // 确认该订单是该用户
        $this->authorize('own', $order);

        // 判断商品是否已经发货
        if ($order->ship_status !== Order::SHIP_STATUS_DELIVERED) {
            throw new InvalidRequestException('发货状态不正确');
        }

        // 更新发货状态为已收到
        $order->update(['ship_status' => Order::SHIP_STATUS_RECEIVED]);

        return $order;
    }

    public function review(Order $order)
    {
        $this->authorize('own', $order);
        // 判断是否已经支付
        if (!$order->paid_at)
        {
            throw new InvalidRequestException('该订单尚未支付，不可以评价');
        }
        
        return view('orders.review', ['order' => $order->load('items.productSku', 'items.product')]);
    }

    public function sendReview(Order $order, SendReviewRequest $request)
    {
        $this->authorize('own', $order);
        // 判断是否已经支付
        if (!$order->paid_at)
        {
            throw new InvalidRequestException('该订单尚未支付，不可以评价');
        }
        // 判断该订单是否已经评价过了
        if ($order->reviewed) {
            throw new InvalidRequestException('不可以重复评论');
        }

        $reviews = $request->input('reviews');
        // 开启事务
        \DB::transaction(function () use ($reviews, $order) {
            // 遍历用户提交的数据
            foreach ($reviews as $review) {
                $orderItem = $order->items()->find($review['id']);

                // 保存评分和评价
                $orderItem->update([
                    'rating' => $review['rating'],
                    'review' => $review['review'],
                    'reviewed_at' => Carbon::now()
                ]);
            }
            $order->update(['reviewed' => true]);
        });

        $this->afterReview($order);

        return redirect()->back();
    }

    public function afterReview(Order $order)
    {
        event(new OrderReviewed($order));
    }

    // 申请退款
    public function applayRefund(Order $order, ApplyRefundRequest $request)
    {
        $this->authorize('own', $order);

        if (!$order->paid_at) {
            throw new InvalidRequestException('该订单尚未付款,不可申请退款!');
        }

        if ($order->refund_status !== Order::REFUND_STATUS_PENDING) {
            throw new InvalidRequestException('该订单已经申请了退款，请勿重复申请');   
        }

        // 将用户申请退款的理由存放在extra字段中
        $extra = $order->extra ?: [];
        $extra['refund_reason'] = $request->input('reason');

        // 更新退款状态
        $order->update([
            'refund_status' => Order::REFUND_STATUS_APPLIED,
            'extra' => $extra
        ]);

        return $order;
    }
}
