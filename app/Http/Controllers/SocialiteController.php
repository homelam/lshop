<?php

namespace App\Http\Controllers;

use Socialite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SocialiteController extends Controller
{
    use AuthenticatesUsers;

    const SOCIAL_ACCOUNT_PASSWORD = '123456';

    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return Response
     */
    public function redirectToProvider(Request $request, $driver)
    {
        return Socialite::driver($driver)->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return Response
     */
    public function handleProviderCallback(Request $request, $driver)
    {
        $user = Socialite::driver($driver)->user();
        // NOTE: 获取用户后 逻辑
        $userinfo = User::where('social_id', $user->getId())->first();
        // 1. 如果是新用户，则需要自动注册后
        if (!$userinfo) {
            $data = [
                'email' => $user->getEmail(),
                'name' => $user->getName(), 
                'nickname' => $user->getNickname(),
                'avatar' => $user->getAvatar(),
                'social_id' => $user->getId(),
                'provider' => $user->getProviderName(),
                'password' => bcrypt(self::SOCIAL_ACCOUNT_PASSWORD) // 社交登录的初始密码
            ];
            $userinfo = User::create($data);
        }

        // 2. 根据获取的用户信息进行用户登录
        if (Auth::attempt(['social_id' => $userinfo->social_id, 'password' => self::SOCIAL_ACCOUNT_PASSWORD])) {
            $user = Auth::user(); // 获取登录的用户
            $user->update([
                'last_login_ip' => $request->ip(),
                'last_login_at' => Carbon::now()
            ]);
            return redirect()->intended('/'); // 登录成功跳转至首页
        }

        // 如果登录失败跳回登录页
        return redirect()->route('login');
    }
}
