<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CouponCode;
use App\Exceptions\CouponCodeUnavailableException;

class CouponCodesController extends Controller
{
    //
    public function show(Request $request, $code)
    {
        // 判断优惠券是否存在
        if (!$record = CouponCode::where('code', $code)->first()) {
            throw new CouponCodeUnavailableException('优惠券不存在');
        }
        
        // NOTE: 判断优惠券合法性
        $record->checkAvailable($request->user());

        return $record;
    }
}
