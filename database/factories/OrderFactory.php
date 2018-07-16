<?php

use App\Models\{CouponCode, User, Order};
use Faker\Generator as Faker;

$factory->define(Order::class, function (Faker $faker) {
    $user = User::query()->inRandomOrder()->first(); // 随机抽取一个用户
    $address = $user->addresses()->inRandomOrder()->first(); // 从该用户随机抽取一个收货地址
    $refund = random_int(0, 10) < 1; // 10%的概率把订单标记为退款
    $ship = $faker->randomElement(array_keys(Order::$shipStatusMap));
    $coupon = null;

    $payment_method = $faker->randomElement([Order::$paymentMethodMap['alipay'], Order::$paymentMethodMap['wechat']]);
    
    if (random_int(0, 10) < 3) {
        // 只选择没有最低金额限制的优惠券
        $coupon = CouponCode::query()->where('min_amount', 0)->inRandomOrder()->first();
        // 修改优惠券使用数量
        $coupon->changeUsed();
    }

    return [
        'address' => [
            'address' => $address->full_address,
            'zip' => $address->zip,
            'contact_name' => $address->contact_name,
            'contact_phone' => $address->contact_phone,
        ],
        'total_amount' => 0,
        'remark' => $faker->sentence,
        'paid_at' => $faker->dateTimeBetween('-15days'),
        'payment_method' => $payment_method,
        'payment_no' => $faker->uuid,
        'refund_status' => $refund ? Order::REFUND_STATUS_SUCCESS : Order::REFUND_STATUS_PENDING,
        'refund_no' => $refund ? Order::getAvailableRefundNo() : null,
        'closed' => false,
        'reviewed' => random_int(0, 10) > 3,
        'ship_status' => $ship,
        'ship_data' => $ship === Order::SHIP_STATUS_PENDING ? null : [
            'express_company' => $faker->company,
            'express_no' => $faker->uuid,
        ],
        'extra' => $refund ? ['refund_reason' => $faker->sentence] : [],
        'user_id' => $user->id,
        'coupon_code_id' => $coupon ? $coupon->id : null,
    ];
});
