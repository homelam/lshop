<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::redirect('/', '/products')->name('root');
Auth::routes();

Route::group(['middleware' => 'auth'], function() {
    Route::get('/email_verify_notice', 'PagesController@emailVerifyNotice')->name('email_verify_notice');
    // 开始
    // Route::group(['middleware' => 'email_verified'], function() {
    //     Route::get('/test', function() {
    //         return 'Your email is verified';
    //     });
    // });
    
    Route::get('/email_verification/verify', 'EmailVerificationController@verify')->name('email_verification.verify');
    Route::get('/email_verification/send', 'EmailVerificationController@send')->name('email_verification.send');
    
    Route::group(['middleware' => 'email_verified'], function() {
        // 收货地址
        Route::get('user_addresses', 'UserAddressesController@index')->name('user_addresses.index');
        Route::get('user_addresses/create', 'UserAddressesController@create')->name('user_addresses.create');
        Route::post('user_addresses', 'UserAddressesController@store')->name('user_addresses.store');
        Route::get('user_addresses/{user_address}', 'UserAddressesController@edit')->name('user_addresses.edit');
        Route::put('user_addresses/{user_address}', 'UserAddressesController@update')->name('user_addresses.update');
        Route::delete('user_addresses/{user_address}', 'UserAddressesController@destroy')->name('user_addresses.destroy');

        // 商品收藏
        Route::post('products/{product}/favorite', 'ProductsController@favor')->name('products.favor');
        Route::delete('products/{product}/favorite', 'ProductsController@disfavor')->name('products.disfavor');
        // 用户商品收藏列表
        Route::get('products/favorites', 'ProductsController@favorites')->name('products.favorites');

        // 添加购物车
        Route::post('cart', 'CartController@add')->name('cart.add');
        Route::get('cart', 'CartController@index')->name('cart.index');       
        Route::delete('cart/{sku}', 'CartController@remove')->name('cart.remove');   
        
        // 订单
        Route::post('orders', 'OrdersController@store')->name('orders.store');
        // 订单列表
        Route::get('orders', 'OrdersController@index')->name('orders.index');
        Route::get('orders/{order}', 'OrdersController@show')->name('orders.show');
        Route::post('orders/{order}/received', 'OrdersController@received')->name('orders.received'); // 用户确认收货

        Route::get('orders/{order}/review', 'OrdersController@review')->name('orders.review.show'); // 评价页面显示
        Route::post('orders/{order}/review', 'OrdersController@sendReview')->name('orders.review.store'); // 保存用户评价评分

        // 申请退款
        Route::post('orders/{order}/refund', 'OrdersController@applayRefund')->name('orders.apply_refund');
        
        // 订单支付
        Route::get('payment/{order}/alipay', 'PaymentController@payByAlipay')->name('payment.alipay');
        Route::get('payment/alipay/return', 'PaymentController@alipayReturn')->name('payment.alipay.return');
        Route::get('payment/{order}/wechat', 'PaymentController@payByWechat')->name('payment.wechat');

        // 优惠券
        Route::get('coupon_codes/{code}', 'CouponCodesController@show')->name('coupon_codes.show');
    });
});

Route::post('payment/alipay/notify', 'PaymentController@alipayNotify')->name('payment.alipay.notify');
Route::post('payment/wechat/notify', 'PaymentController@wechatNotify')->name('payment.wechat.notify');
Route::post('payment/wechat/refund_notify', 'PaymentController@wechatRefundNotify')->name('payment.wechat.refund_notify'); // 微信退款回调

Route::get('products', 'ProductsController@index')->name('products.index');
Route::get('products/{product}', 'ProductsController@show')->name('products.show');

Route::get('oauth/{driver}', 'SocialiteController@redirectToProvider')->name('social.login');
Route::get('oauth/{driver}/callback', 'SocialiteController@handleProviderCallback');
