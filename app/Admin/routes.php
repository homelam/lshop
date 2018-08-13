<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');

    // 会员管理
    $router->resources([
        'users' => MembersController::class,
    ]);
    
    // 商品管理模块
    $router->get('products', 'ProductsController@index');
    $router->get('products/create', 'ProductsController@create');
    $router->post('products', 'ProductsController@store');
    $router->get('products/{id}/edit', 'ProductsController@edit');
    $router->put('products/{id}', 'ProductsController@update');

    // 订单管理
    $router->get('orders', 'OrdersController@index')->name('admin.orders.index');
    $router->get('orders/{order}', 'OrdersController@show')->name('admin.orders.show');
    $router->post('orders/{order}/ship', 'OrdersController@ship')->name('admin.orders.ship');

    // 处理退款
    $router->post('orders/{order}/refund', 'OrdersController@handleRefund')->name('admin.orders.handle_refund');

    // 优惠券
    $router->get('coupon_codes', 'CouponCodesController@index');
    $router->post('coupon_codes', 'CouponCodesController@store');
    $router->get('coupon_codes/create', 'CouponCodesController@create');
    $router->get('coupon_codes/{id}/edit', 'CouponCodesController@edit');
    $router->put('coupon_codes/{id}', 'CouponCodesController@update');
    $router->delete('coupon_codes/{id}', 'CouponCodesController@destroy');

    // 品牌管理模块
    $router->get('brands', 'BrandsController@index');
    $router->get('brands/create', 'BrandsController@create');
    $router->post('brands', 'BrandsController@store');
    $router->get('brands/{id}/edit', 'BrandsController@edit');
    $router->put('brands/{id}', 'BrandsController@update');
    $router->delete('brands/{id}', 'BrandsController@destroy');

    // 分类管理模块
    $router->resource('categories', 'CategoriesController');
});
