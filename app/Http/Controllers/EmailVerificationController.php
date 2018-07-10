<?php

namespace App\Http\Controllers;

use App\Models\User;
use Cache;
use Illuminate\Http\Request;
use App\Notifications\EmailVerificationNotification;
use App\Exceptions\InvalidRequestException;

class EmailVerificationController extends Controller
{
    //
    public function verify(Request $request)
    {
        // 从 url 中获取 `email` 和 `token` 参数
        $email = $request->input('email');
        $token = $request->input('token');
        
        if (!$email || !$token) {
            throw new InvalidRequestException('验证链接不正确');
        }
        
        // 从缓存中读取数据，我们把url 中获取的`token`与缓存中的值做对比
        // 如果缓存中不存在获取返回的url中的`token`不一致就抛出异常
        if ($token != Cache::get('email_verification_'.$email)) {
            throw new InvalidRequestException('验证链接不正确或者已经过期');
        }

        // 根据邮箱从数据库中获取对应的用户
        // 通常来说能通过 token 校验的情况下不可能出现用户不存在
        // 但是为了代码的健壮性我们还是需要做这个判断
        if (!$user = User::where('email', $email)->first()) {
            throw new InvalidRequestException('用户不存在');
        }
        
        // 将指定的key 从缓存中删除，由于已经完成了验证，这个缓存就没必要保留了
        Cache::forget('email_verification_'.$email);

        $user->update(['email_verified' => true]);

        // 最后告知用户邮箱验证成功
        return view('pages.success', ['msg' => '邮箱验证成功']);
    }

    // 用户手动发送激活邮件的入口
    public function send(Request $request)
    {
        $user = $request->user();

        // 判断用户是否已经激活
        if ($user->email_verified) {
            throw new InvalidRequestException('你已经验证过邮箱了');
        }

        // 调用notify() 方法用来发送我们定义好的通知类
        $user->notify(new EmailVerificationNotification()); 

        return view('pages.success', ['msg' => '邮件发送成功']);
    }
}
