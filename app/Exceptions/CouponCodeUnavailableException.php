<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;

class CouponCodeUnavailableException extends Exception
{
    protected $message;
    protected $code;
    
    public function __construct($message, int $code = 403)
    {
        $this->message = $message;
        $this->code = $code;
        
        parent::__construct($message, $code);
    }

    // 当这个异常被触发时，会调用render 方法来输出给用户
    public function render(Request $request)
    {
        // 如果用户通过Api 请求，则返回JSON格式的错误信息
        if ($request->expectsJson()) {
            return response()->json(['msg' => $this->message], $this->code);
        }

        return redirect()->back()->withErrors(['coupon_code' => $this->message]);
    }
}
