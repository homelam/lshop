<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Exceptions\CouponCodeUnavailableException;

class CouponCode extends Model
{
    // 用常量的方式定义优惠券类型
    const TYPE_FIXED = 'fixed'; // 固定金额
    const TYPE_PERCENT = 'percent'; // 百分比优惠

    public static $typeMap = [
        self::TYPE_FIXED => '固定金额',
        self::TYPE_PERCENT => '百分比优惠'
    ];

    protected $fillable = [
        'name', 'code', 'type', 'value', 'total', 'used', 'min_amount', 'not_before', 'not_after', 'enabled'
    ];

    protected $appends = ['description'];

    protected $dates = ['not_before', 'not_after'];

    public static function generateAvailableCode($length = 16)
    {
        do {
            // 生成一个指定长度的随机字符串，并转成大写
            $code = strtoupper(Str::random($length));
        // 如果生成的码已存在就继续循环
        } while (self::query()->where('code', $code)->exists());

        return $code;
    }

    public function getDescriptionAttribute()
    {
        $str = '';
        if ($this->min_amount > 0) {
            $str = '满'.numberFormat($this->min_amount);
        } 
        if ($this->type === self::TYPE_PERCENT) {
            return $str . '优惠' . numberFormat($this->value) . '%';
        }

        return $str . '减' . numberFormat($this->value);
    }

    // 检测优惠码是否可用
    public function checkAvailable(User $user, $orderAmount = null)
    {
        if (!$this->enabled) {
            throw new CouponCodeUnavailableException('优惠券不可用', 404);
        }
        if ($this->total - $this->used <= 0) {
            throw new CouponCodeUnavailableException('优惠券已被兑换完');
        }
        // 判断优惠券使用时间
        if ($this->not_before && $this->not_before->gt(Carbon::now())) {
            throw new CouponCodeUnavailableException('该优惠券尚不可用');
        }
        if ($this->not_after && $this->not_after->lt(Carbon::now())) {
            throw new CouponCodeUnavailableException('该优惠券已经过期');
        }

        if (!is_null($orderAmount) && $orderAmount < $this->min_amount) {
            throw new CouponCodeUnavailableException('该订单不满足优惠券的最低金额');
        }

        // 每个用户每个优惠券只能使用一次
        // 使用： 未付款且未关闭订单或者已付款且未退款订单
        $used = Order::where('user_id', $user->id)
            ->where('coupon_code_id', $this->id)
            ->where(function($query) {
                $query->where(function($query) {
                    $query->whereNull('paid_at')
                        ->where('closed', false);
                })->orWhere(function($query) {
                    $query->whereNotNull('paid_at')
                        ->where('refund_status', Order::REFUND_STATUS_PENDING);
                });
            })
            ->exists();
        if ($used) {
            throw new CouponCodeUnavailableException('你已经使用过这张优惠券了');
        }
    }

    // 获取订单优惠后的金额
    public function getAdjustedPrice($orderAmount)
    {
        // 如果是固定金额
        if ($this->type == self::TYPE_FIXED) {
            return max(0.01, $orderAmount - $this->value);
        }

        return number_format($orderAmount * (100 - $this->value)/100, 2, '.', '');
    }

    // 修改优惠券的使用
    public function changeUsed($increase = true)
    {
        // 传入 true 代表新增用量，否则是减少用量, 取消订单 或者 申请退货
        if ($increase) {
            // 与检查 SKU 库存类似，这里需要检查当前用量是否已经超过总量
            return $this->newQuery()->where('id', $this->id)->where('used', '<', $this->total)->increment('used');
        } else {
            return $this->decrement('used');
        }
    }
}
