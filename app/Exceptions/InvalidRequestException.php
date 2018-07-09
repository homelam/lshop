<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;

class InvalidRequestException extends Exception
{
    // 错误信息
    protected $message;
    
    public function __construct(string $message = '', int $code = 400)
    {
        parent::__construct();
        $this->message = $message;
    }

    public function render(Request $request)
    {
        if ($request->expectsJson()) {
            // json() 方法的第二个参数就是Http返回码
            return response()->json(['msg' => $this->message], $this->code);
        }
        
        return view('pages.error', ['msg' => $this->message]);
    }
}
