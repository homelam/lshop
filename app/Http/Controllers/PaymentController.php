<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Exceptions\InvalidRequestException;
use Endroid\QrCode\QrCode;
use App\Events\OrderPaid;


class PaymentController extends Controller
{
    //
    public function payByAlipay(Order $order, Request $request)
    {
        // 判断订单是否是属于当前用户的
        $this->authorize('own', $order);

        // 判断订单是否已经支付或应关闭
        if ($order->paid_at || $order->closed) {
            throw new InvalidRequestException('订单状态不正确');
        }

        // 调用支付宝的网页支付功能
        return app('alipay')->web([
            'out_trade_no' => $order->order_no,
            'total_amount' => $order->total_amount,
            'subject' => '支付' . env('APP_NAME', 'DCONLINE') . '的订单：'.$order->order_no
        ]);
    }

    public function alipayReturn()
    {
        try {
            // 校验提交的参数是否合法
            $data = app('alipay')->verify();
        } catch (\Exception $e) {
            return view('pages.error', ['msg' => '数据不正确']);
        }

        return view('pages.success', ['msg' => '支付成功']);
    }

    // 服务器端回调
    public function alipayNotify()
    {
        $data = app('alipay')->verify();

        //$data->order_trade_no 拿到订单流水号，并在数据库中查询
        $order = Order::where('order_no', $data->order_trade_no)->first();
        if (!$order) {
            return 'fail';
        } 
        // 如果这笔订单的状态是已经支付的
        if ($order->paid_at) {
            // 把数据返回给支付宝
            return app('alipay')->success();
        }

        // 更新订单信息
        $order->update([
            'paid_at' => Carbon::now(),
            'payment_method' => 'alipay',
            'payment_no' => $data->trade_no // 支付宝订单号
        ]);
        
        // NOTE: 支付成功后触发事件
        $this->afterPaid($order);

        return app('alipay')->success();
    }

    public function payByWechat(Order $order, Request $request)
    {
        // 判断订单是否是属于当前用户的
        $this->authorize('own', $order);
        
        // 判断订单是否已经支付或已关闭
        if ($order->paid_at || $order->closed) {
            throw new InvalidRequestException('订单状态不正确');
        }

        // scan 方法拉起微信扫码支付
        $wechatOrder = app('wechat')->scan([
            'out_trade_no' => $order->order_no,
            'total_fee' => $order->tatal_amount * 100,
            'body' => '支付' . env('APP_NAME', 'DCONLINE') . '的订单：'.$order->order_no // 订单描述
        ]);

        // 把字符串转成用户扫描的二维码
        $qrCode = new QrCode($wechatOrder->code_url);

        // 将生成的二维码图片数据以字符串形式输出，并带上相应的响应类型
        return response($qrCode->writeString(), 200, ['Content-Type' => $qrcODE->getContentType()]);
    }

    public function wechatNotify()
    {
        // 校验回调参数
        $data = app('wechat')->verify();

        // 找到对应的订单
        $order = Order::where('order_no', $data->out_trade_no)->first();
        if (!$order) {
            return 'fail';
        } 
        // 如果这笔订单的状态是已经支付的
        if ($order->paid_at) {
            // 把数据返回给微信，此订单已经处理
            return app('wechat')->success();
        }
        
        // 更新订单信息
        $order->update([
            'paid_at' => Carbon::now(),
            'payment_method' => 'alipay',
            'payment_no' => $data->trade_no // 支付宝订单号

        ]);
        
        // NOTE: 支付成功后触发时间
        $this->afterPaid($order);

        return app('wechat')->success();
    }

    public function afterPaid(Order $order)
    {
        event(new OrderPaid($order));
    }

    // wechat 退款回调通知
    public function weichatRefundNotify(Request $request)
    {
        // 给微信的失败响应
        $failXml = '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[FAIL]]></return_msg></xml>';
        // 把请求的 xml 内容解析成数组
        $input = parse_xml($request->getContent());
        // 如果解析失败或者没有必要的字段，则返回错误
        if (!$input || !isset($input['req_info'])) {
            return $failXml;
        }
        // 对请求中的 req_info 字段进行 base64 解码
        $encryptedXml = base64_decode($input['req_info'], true);
        // 对解码后的 req_info 字段进行 AES 解密
        $decryptedXml = openssl_decrypt($encryptedXml, 'AES-256-ECB', md5(config('pay.wechat.key')), OPENSSL_RAW_DATA, '');
        // 如果解密失败则返回错误
        if (!$decryptedXml) {
            return $failXml;
        }
        // 解析解密后的 xml
        $decryptedData = parse_xml($decryptedXml);
        
        if(!$order = Order::where('order_no', $decryptedData['out_trade_no'])->first()) {
            return $failXml;
        }

        if ($decryptedData['refund_status'] === 'SUCCESS') {
            // 退款成功，将订单退款状态改成退款成功
            $order->update([
                'refund_status' => Order::REFUND_STATUS_SUCCESS,
            ]);
        } else {
            // 退款失败，将具体状态存入 extra 字段，并表退款状态改成失败
            $extra = $order->extra;
            $extra['refund_failed_code'] = $decryptedData['refund_status'];
            $order->update([
                'refund_status' => Order::REFUND_STATUS_FAILED,
            ]);
        }

        return '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
    }
}
