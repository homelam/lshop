<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    // 退款状态
    const REFUND_STATUS_PENDING = 'pending';
    const REFUND_STATUS_APPLIED = 'applied';
    const REFUND_STATUS_PROCESSING = 'processing';
    const REFUND_STATUS_SUCCESS= 'success';
    const REFUND_STATUS_FAILED= 'failed';

    // 运输状态
    const SHIP_STATUS_PENDING = 'pending';
    const SHIP_STATUS_DELIVERED = 'delivered';
    const SHIP_STATUS_RECEIVED = 'received';

    public static $shipStatusMap = [
        self::SHIP_STATUS_PENDING => '未发货',
        self::SHIP_STATUS_DELIVERED => '已发货',
        self::SHIP_STATUS_RECEIVED => '已签收'
    ];

    protected $fillable = [
        'order_no',
        'address',
        'total_amount',
        'remark',
        'paid_at',
        'payment_menthod',
        'payment_no',
        'refund_status',
        'refund_no',
        'closed',
        'reviewed',
        'ship_status',
        'ship_data',
        'extra',
    ];

    protected $casts = [
        'closed'    => 'boolean',
        'reviewed'  => 'boolean',
        'address'   => 'json',
        'ship_data' => 'json',
        'extra'     => 'json',
    ];

    protected $dates = ['paid_at'];

    protected static function boot()
    {
        parent::boot();

        // 监听模型创建事件，在写入数据库前触发
        static::creating(function($model) {
            // 如果订单流水号为空的话
            if (!$model->order_no) {
                // 调用方法生成订单号
                $model->order_no = static::generateAvailableNo();

                // 如果创建失败，终止订单的创建
                if (!$model->order_no) {
                    return false;
                }
            }
        });
    }

    public static function generateAvailableNo($length = 6)
    {
        $prefix = date('YmdHis');
        
        // 随机生成 6 位的数字
        $order_no = $prefix.str_pad(random_int(0, 999999), $length, '0', STR_PAD_LEFT);
        // 判断是否已经存在
        if (!static::query()->where('order_no', $order_no)->exists()) {
            return $order_no;
        }

        \Log::warning('find order no failed');
        
        return false;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}