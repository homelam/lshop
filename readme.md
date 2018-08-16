# SHOP 项目

项目[Github](https://github.com/homelam/lshop.git)主要包括了一下几部分功能：

[TOC]

---------------

- **用户登录注册**
- **用户收货地址管理**
- **商城平台管理后台**
- **管理员的权限管理**
- **商品模块**
- **商品品牌管理模块**
- **商品分类管理模块**
- **商品sku设计**
- **会员管理模块**
- **第三方登录模块**
- **购物车模块**
- **支付模块**
- **订单商品的评价**
- **商品收藏功能**
- **订单退款**
- **商城优惠券模块**
- **用户收货地址管理**

-------------------

## 项目概述

> 使用Laravel-Admin快速构建商品管理的后台、支付宝和微信支付等回调通知处理，在项目中自定义对异常的处理、购物车的设计、商品数据结构的设计，通过延迟队列自动关闭订单，利用MySql事务处理、库存增减的正确处理等等的商城系统构建,商品的创建，用户下单，优惠券的创建使用等功能

## 项目需求

本项目主要是简单的从产品用例的角度上分析商城的需求，主要是一下三种元素入手

1.角色

+ 在商城里，通常会出现以下几种角色：
	+ 游客 —— 没有登录的用户；
	+ 用户 —— 注册用户， 可以购买商品；
	+ 运营 —— 可以上架、下架商品，处理订单；
	+ 管理员 —— 权限最高的用户角色，可以管理运营。

2.信息结构

主要信息有：

+ 用户 —— 模型名称 User，包括的用户登录注册；
+ 收货地址 —— 模型名称 UserAddress，包含地址和收货人姓名、电话；
+ 商品 —— 模型名称 Product，比如 iPhone X 就是一个商品；
+ 商品 SKU —— 模型名称 ProductSKU，同一商品下有个别属性可能有不同的值，比如 iPhone X 256G 和 iPhone X 64G 就是同一个商品的不同 SKU，每个 SKU 都有各自独立的库存；
+ 商品分类 —— 模型名称 Category
+ 商品品牌 —— 模型名称 Brand
+ 订单 —— 模型名称 Order；
+ 订单项 —— 模型名称 OrderItem，一个订单会包含一个或多个订单项，每个订单项都会与一个商品 SKU 关联；
+ 优惠券 —— 模型名称 CouponCode，订单可以使用优惠券来扣减最终需要支付的金额；
+ 运营人员 —— 模型名称 Operator，管理员也属于运营人员。
+ 社交登录 —— 模型名称 Socialite, 利用社交媒体帐号登录注册商城

3.动作

角色和信息之间的互动称之为动作，主要有：

+ 增 Create
+ 删 Delete
+ 改 Update
+ 查 Read

## 准备分析

**1. 模块清单**

基于我们的需求分析，我们将系统拆分成如下几大模块：

- 用户模块
- 商品模块
- 订单模块
- 支付模块
- 优惠券模块
- 管理模块

**2. 依赖关系**

有了模块清单，接下来我们需要思考，他们之间的依赖关系是怎样的。在上面的功能清单中，『订单模块』依赖于『用户模块』和『商品模块』，『支付模块』和『优惠券模块』又依赖于『订单模块』。各个模块之间的依赖关系可以用下图来表示：


**3. 依赖关系**

各个模块开发的顺序如下：

1. 用户模块
2. 商品模块
3. 分类模块
4. 品牌模块
5. 订单模块
6. 支付模块
7. 优惠券模块
8. 第三方登录模块

『管理模块』是一个特殊的模块，既包含本身的逻辑（管理后台的权限控制等），又与其他业务模块都有关联，因此在开发过程中会与其他模块穿插开发

## 基础布局

------------ 

### 页面布局

首先为项目构建一个基础的页面布局，布局的文件统一放在`resources/views/layouts`文件夹中：

+ app.blade.php —— 主要布局文件，项目的所有页面都将继承于此页面；
+ _header.blade.php —— 布局的头部区域文件，负责顶部导航栏区块；
+ _footer.blade.php —— 布局的尾部区域文件，负责底部导航区块；

> 布局使用 `Boostrap` 前端布局， 运行Laravel Mix管理前端资源

修改Mix的配置文件：
webpack.mix.js

```php
mix.js('resources/assets/js/app.js', 'public/js')
   .sass('resources/assets/sass/app.scss', 'public/css')
   .version();
```
> 在末尾添加 `version()`，使得没测生成的静态文件后面加上一个类似版本号的参数，避免浏览器缓存，然后自行命令`npm run watch`进行编译

### 辅助函数文件

在开发中，有时候需要创建自己的辅助函数，把所有自定义的辅助函数存放在`bootstrap/helpers.php`文件中，创建文件，并且放入一下内容：

```php
if (!function_exists('test_helper')) {
    function test_helper() {
	    return 'OK';
	}
}
```

打开 `composer.json`文件，并找到`autoload`，将其修改为：

```php
 "autoload": {
        "classmap": [
            "database/seeds",
            "database/factories"
        ],
        "psr-4": {
            "App\\": "app/"
        },
        "files": [
            "bootstrap/helpers.php"
        ]
    },
```
执行命令`composer dumpautoload` 利用tinker `php artisan tinker`执行 test_helper() 函数可以看到正常输出；


## 注册与登录

Laravel 自带了用户认证的功能，利用自带的功能快速构建我们的用户中心。

### 用户注册
首先执行命令： `php artisan make:auth`，生成代码

在商城中可以允许 邮箱或者是手机 登录，以及需要邮箱认证之后才能进行商品的购买，所以需要在用户数据表添加`mobile`以及`email_verified`字段, 执行命令

> php artisan make:migration users_add_fields --table=users

修改用户注册页面和登录页面，添加mobile字段, 重写`credentials`方法，根据输入值的个是自动判断是邮箱还是手机号码：

```php
protected function credentials(Request $request)
    {
        // 如果输入的是邮箱，同样允许登录
        $field = filter_var($request->input($this->username()), FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';

        return [
            $field => $request->input($this->username()),
            'password' => $request->input('password')
        ];
    }
```
在验证邮箱之后才能正常使用我们系统的功能，当用户尚未验证邮箱时，访问其他页面都会被重定向到一个提示验证邮箱的页面可以通过中间件来解决，把需要验证邮箱的路由放到拥有这个中间件的路由组中，当用户访问这些路由时会先执行中间件检查是否验证了邮箱。

> php artisan make:middleware CheckIfEmailVerfiied

```php
 public function handle($request, Closure $next)
    {
        if (!$request->user()->email_verified) {
            // 如果是 AJAX 请求，则通过 JSON 返回
            if ($request->expectsJson()) {
                return response()->json(['msg' => '请先验证邮箱'], 400);
            }
            return redirect(route('email_verify_notice'));
        }
        return $next($request);
    }
```
在`app/Http/kernel.php`中注册邮箱验证中间件：

```php
protected $routeMiddleware = [
        .
        .
        .
        'email_verified' => \App\Http\Middleware\CheckIfEmailVerified::class,
    ];
```

前台需要验证邮箱的路由都可以添加这个中间件下。

### 验证邮箱

如果希望用户注册完成以后自动发送验证邮件，可以创建一个验证邮件通知类，通过Laravel内置的通知模块（Notification）来实现邮件的发送。

> php artisan make:notification EmailVerificationNotification

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class EmailVerificationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    // 我们只需要通过邮件通知，因此这里只需要一个 mail 即可
    public function via($notifiable)
    {
        return ['mail'];
    }

    // 发送邮件时会调用此方法来构建邮件内容，参数就是 App\Models\User 对象
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->greeting($notifiable->name.'您好：')
                    ->subject('注册成功，请验证您的邮箱')
                    ->line('请点击下方链接验证您的邮箱')
                    ->action('验证', url('/'));
    }

    public function toArray($notifiable)
    {
        return [];
    }
}
```

代码解析：

- 在类的申明里我们加上了 implements ShouldQueue，ShouldQueue 这个接口本身没有定义任何方法，对于实现了 ShouldQueue 的邮件类 Laravel 会用将发邮件的操作放进队列里来实现异步发送；
- greeting() 方法可以设置邮件的欢迎词；
- subject() 方法用来设定邮件的标题；
- line() 方法会在邮件内容里添加一行文字；
- action() 方法会在邮件内容里添加一个链接按钮。这里就是激活链接，我们暂时把链接设成了主页，接下来我们来实现这个激活链接的逻辑

实现思路：

> 当发送注册激活邮件时，我们会生成一个随机字符串，然后以邮箱为 Key、随机字符串作为值保存在缓存中，邮箱和这个随机字符串会作为激活链接的参数。当用户点击激活链接时，我们只需要从缓存中取出对应的数据并判断是否一致就可以确定这个激活链接是否正确

在邮件通知控制器中添加用户手动发送验证邮件的入口。

### 自定义处理异常

在实现邮箱验证的时候，在处理一些非正常流程时使用了 throw new Exception 抛出异常来终止流程，如果使用使用laravel的异常通知类，用户体验并不是那么好。
自定义异常类, 通过`make:exception` 命令创建的异常文件保存在 app/Exceptions/ 目录下：

> php artisan make:exception InvalidRequestException

app/Exceptions/InvalidRequestException.php

```php
<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;

class InvalidRequestException extends Exception
{
    public function __construct(string $message = "", int $code = 400)
    {
        parent::__construct($message, $code);
    }

    public function render(Request $request)
    {
        if ($request->expectsJson()) {
            // json() 方法第二个参数就是 Http 返回码
            return response()->json(['msg' => $this->message], $this->code);
        }

        return view('pages.error', ['msg' => $this->message]);
    }
}
```
Laravel 5.5 之后支持在异常类中定义 render() 方法，该异常被触发时系统会调用 render() 方法来输出，我们在 render() 里判断如果是 AJAX 请求则返回 JSON 格式的数据，否则就返回一个自定义的错误页面。
使用方法：

```php
<?php
	use App\Exceptions\InvalidRequestException;
	.
	.
	.
	throw new InvalidRequestException('你已经验证过邮箱了');
```

## 用户收货地址

### 数据库结构

---------------

字段  | 描述  | 类型 | 是否索引
--- | --- | --- | ---
id | 自增长ID | unsigned int | 主键 |
user_id | 该地址所属的用户 |   unsigned int | 外键 |
province | 省 | varchar | 无 |
city | 市 | varchar | 无 |
district | 区 |    varchar | 无 |
address | 具体地址 |    varchar | 无 |
zip | 邮编 | unsigned int | 无 |
contact_name | 联系人姓名        |    varchar | 无 |
contact_phone | 联系人电话        |    varchar | 无 |
last_used_at | 最后使用地址        |    datetime null | 无 |

创建地址模型:

> php artisan make:model Models/UserAddress -fm

`-fm` 参数代表同时生成 `factory 工厂文件`和 `migration 数据库迁移文件`

在模型中添加与用户的模型关联，修改模型文件：
app/Models/UserAddress.php

```php
 protected $fillable = [
        'province',
        'city',
        'district',
        'address',
        'zip',
        'contact_name',
        'contact_phone',
        'last_used_at',
    ];
    protected $dates = ['last_used_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFullAddressAttribute()
    {
        return "{$this->province}{$this->city}{$this->district}{$this->address}";
    }
```
app/Models/User.php
```php
	.
	.
	. 
	public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }
```
工厂文件来自动生成收货地址假数据：
> php artisan tinker

```php
<?php

use Faker\Generator as Faker;

$factory->define(App\Models\UserAddress::class, function (Faker $faker) {
    $addresses = [
        ["北京市", "市辖区", "东城区"],
        ["河北省", "石家庄市", "长安区"],
        ["江苏省", "南京市", "浦口区"],
        ["江苏省", "苏州市", "相城区"],
        ["广东省", "深圳市", "福田区"],
    ];
    $address   = $faker->randomElement($addresses);

    return [
        'province'      => $address[0],
        'city'          => $address[1],
        'district'      => $address[2],
        'address'       => sprintf('第%d街道第%d号', $faker->randomNumber(2), $faker->randomNumber(3)),
        'zip'           => $faker->postcode,
        'contact_name'  => $faker->name,
        'contact_phone' => $faker->phoneNumber,
    ];
});
```
利用工具tinker测试刚创建的工厂文件
```php
>>> factory(App\Models\UserAddress::class)->make()
```

### 新建收货地址

前台用户添加收货地址使用了Vue来实现：添加中国省市区的库

> yarn add china-area-data

这个 nodejs 库包含了（基本上）最新的中国省市区的数据，通过类似邮编的方式将上下级关联起来,，具体数据结构可以参考这个[代码文件](https://github.com/airyland/china-area-data/blob/master/v3/data.js)

创建vue的组件：
resources/assets/js/components/SelectDistrict.js
```javascript
// 从刚刚安装的库中加载数据
const addressData = require('china-area-data/v3/data');
// 引入 lodash，lodash 是一个实用工具库，提供了很多常用的方法
import _ from 'lodash';

// 注册一个名为 select-district 的 Vue 组件
Vue.component('select-district', {
  // 定义组件的属性
  props: {
    // 用来初始化省市区的值，在编辑时会用到
    initValue: {
      type: Array, // 格式是数组
      default: () => ([]), // 默认是个空数组
    }
  },
  // 定义了这个组件内的数据
  data() {
    return {
      provinces: addressData['86'], // 省列表
      cities: {}, // 城市列表
      districts: {}, // 地区列表
      provinceId: '', // 当前选中的省
      cityId: '', // 当前选中的市
      districtId: '', // 当前选中的区
    };
  },
  // 定义观察器，对应属性变更时会触发对应的观察器函数
  watch: {
    // 当选择的省发生改变时触发
    provinceId(newVal) {
      if (!newVal) {
        this.cities = {};
        this.cityId = '';
        return;
      }
      // 将城市列表设为当前省下的城市
      this.cities = addressData[newVal];
      // 如果当前选中的城市不在当前省下，则将选中城市清空
      if (!this.cities[this.cityId]) {
        this.cityId = '';
      }
    },
    // 当选择的市发生改变时触发
    cityId(newVal) {
      if (!newVal) {
        this.districts = {};
        this.districtId = '';
        return;
      }
      // 将地区列表设为当前城市下的地区
      this.districts = addressData[newVal];
      // 如果当前选中的地区不在当前城市下，则将选中地区清空
      if (!this.districts[this.districtId]) {
        this.districtId = '';
      }
    },
    // 当选择的区发生改变时触发
    districtId() {
      // 触发一个名为 change 的 Vue 事件，事件的值就是当前选中的省市区名称，格式为数组
      this.$emit('change', [this.provinces[this.provinceId], this.cities[this.cityId], this.districts[this.districtId]]);
    },
  },
  // 组件初始化时会调用这个方法
  created() {
    this.setFromValue(this.initValue);
  },
  methods: {
    // 
    setFromValue(value) {
      // 过滤掉空值
      value = _.filter(value);
      // 如果数组长度为0，则将省清空（由于我们定义了观察器，会联动触发将城市和地区清空）
      if (value.length === 0) {
        this.provinceId = '';
        return;
      }
      // 从当前省列表中找到与数组第一个元素同名的项的索引
      const provinceId = _.findKey(this.provinces, o => o === value[0]);
      // 没找到，清空省的值
      if (!provinceId) {
        this.provinceId = '';
        return;
      }
      // 找到了，将当前省设置成对应的ID
      this.provinceId = provinceId;
      // 由于观察器的作用，这个时候城市列表已经变成了对应省的城市列表
      // 从当前城市列表找到与数组第二个元素同名的项的索引
      const cityId = _.findKey(addressData[provinceId], o => o === value[1]);
      // 没找到，清空城市的值
      if (!cityId) {
        this.cityId = '';
        return;
      }
      // 找到了，将当前城市设置成对应的ID
      this.cityId = cityId;
      // 由于观察器的作用，这个时候地区列表已经变成了对应城市的地区列表
      // 从当前地区列表找到与数组第三个元素同名的项的索引
      const districtId = _.findKey(addressData[cityId], o => o === value[2]);
      // 没找到，清空地区的值
      if (!districtId) {
        this.districtId = '';
        return;
      }
      // 找到了，将当前地区设置成对应的ID
      this.districtId = districtId;
    }
  }
});
```
最后在 app.js 中引入这个组件：

resources/assets/js/app.js

```javascript
// 此处需在引入 Vue 之后引入
require('./components/SelectDistrict');

const app = new Vue({
    el: '#app'
});
```
### 优化交互

通过 yarn 引入 sweetalert 这个库，sweetalert 可以用来展示比较美观的弹出提示框：
> yarn add sweetalert
 
然后在boostrap.js引入该库：
resources/assets/js/bootstrap.js

```javascript
require('sweetalert');
.
.
.
```
确保npm run watch 在运行

通过 JS 来调用 sweetalert 弹出二次确认提示框，具体用法请[参考]();

### 检查权限

接下来我们要增加权限控制，只允许地址的拥有者来修改和删除地址，这里通过授权策略类（Policy）来实现权限控制

>  php artisan make:policy UserAddressPolicy

新创建的 Policy 文件位于 app/Policies 目录下。
在 UserAddressPolicy 类中新建一个 own() 方法, 当 own() 方法返回 true 时代表当前登录用户可以修改对应的地址。
app/Policies/UserAddressPolicy.php

```php
use App\Models\UserAddress;
.
.
.
    public function own(User $user, UserAddress $address)
    {
        return $address->user_id == $user->id;
    }
```
在 `AuthServiceProvider` 注册这个授权策略：
app/Providers/AuthServiceProvider.php

```php
use App\Models\UserAddress;
use App\Policies\UserAddressPolicy;
.
.
.
    protected $policies = [
        UserAddress::class => UserAddressPolicy::class,
    ];
.
.
.
```

使用：

```php
    public function destroy(UserAddress $user_address)
    {
        $this->authorize('own', $user_address);
        .
        .
        .
    }
```

## 商城后台

### 安装laravel-admin

**1.安装**

> composer require encore/laravel-admin "1.5.*"
> php artisan vendor:publish --provider="Encore\Admin\AdminServiceProvider"
> php artisan admin:install

**2.配置文件**

config/admin.php

管理后台的超级管理元的帐号密码是： admin:admin

### 用户列表

laravel-admin 的控制器的创建方式名为为：`admin:make`
> php artisan admin:make UsersController --model=App\\Models\\User

laravel-admin的使用[文档](http://laravel-admin.org/docs/#/zh/model-form-fields)

后台的列表以及表单创建使用laravel-admin 构建显示列表和创建列表。

添加路由：
app/Admin/routes.php

```php
<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {
    $router->get('/', 'HomeController@index');
    $router->get('users', 'UsersController@index');
});
```
`注意:` 需要手动创建后台显示的菜单以及管理用户的权限

### 管理员

#### 权限设置： 
进入管理后台，点击左侧菜单的 `系统管理` -> `权限`，点击 新增 按钮

#### 新建角色：
点击左侧菜单的 `系统管理` -> `角色`，点击 新增 按钮

#### 新增管理后台用户

点击左侧菜单的 `系统管理` -> `管理员`，点击 新增 按钮

>我们这里通过角色来把用户和权限关联起来，而不是直接把用户和权限关联，这是因为运营的角色可能会有多个用户，假如运营角色的权限有变化，我们只需要修改运营角色的权限而不需要去修改每个运营用户的权限。
 
 ------------------
 
## 商品的数据结构

### 数据表：

* products 表，产品信息表，对应数据模型 Product 
* product_skus 表，产品的 SKU 表，对应数据模型 ProductSku 

### 字段设置

接下来我们需要整理好 products 表和 product_skus 表的字段名称和类型：

products 表：

字段  | 描述  | 类型 | 是否索引
--- | --- | --- | ---
id     | 自增长ID | unsigned int | 主键 |
name      | 商品名称	        |   varchar | 无 |
description | 商品详情        |    text | 无 |
image | 商品封面图片文件路径        |    varchar | 无 |
on_sale | 商品是否正在售卖       |    tiny int, default 1| 无 |
rating | 商品平均评分       |    float, default 5 | 无 |
sold_count | 销量        |    unsigned int, default 0| 无 |
review_count | 评价数量        |    unsigned int, default 0 | 无 |
price | SKU 最低价格        |    decimal | 无 |

商品本身没有固定的价格，我们在商品表放置 price 字段的目的是方便用户搜索、排序。

product_skus 表：

字段  | 描述  | 类型 | 是否索引
--- | --- | --- | ---
id     | 自增长ID | unsigned int | 主键 |
name      | SKU 名称	        |   varchar | 无 |
description | SKU 描述       |    varchar | 无 |
price | SKU 价格        |    varchar | 无 |
stock | 库存       |    unsigne int| 无 |
product_id | 所属商品 id       |    unsigne int | 外键 |
picture | sku图片      |    varchar | 无 |

创建模型：

> php artisan make:model Models/Product -mf
> php artisan make:model Models/Product -mf

添加模型关联
app/Models/Product.php

```php
    // 与商品SKU关联
    public function skus()
    {
        return $this->hasMany(ProductSku::class);
    }
```

app/Models/ProductSku.php
```php
    // 与商品SKU关联
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
```

由于`商品` 与 `sku` 属于一堆多的关系，laravel-admin 提供了这样关系的数据添加

```php
    $form->hasMany('skus', 'SKU 列表', function (Form\NestedForm $form) {
        $form->text('sku', 'SKU 名称')->rules('required');
        $form->text('description', 'SKU 描述')->rules('required');
        $form->text('price', '单价')->rules('required|numeric|min:0.01');
        $form->text('stock', '剩余库存')->rules('required|integer|min:0');
        $form->image('picture', 'Picture');
    });
    .
    .
    .
    // 定义事件回调，当模型即将保存时会触发这个回调， 计算商品的最低价格作为该商品的原件
    $form->saving(function (Form $form) {
        $form->model()->price = collect($form->input('skus'))->where(Form::REMOVE_FLAG_NAME, 0)->min('price');
    });
```

问题：添加是商品，看到别的数据都正常，但是图片显示不出来：
原因：我们上传文件的都是存储在 storage 目录下，而 HTTP 服务器指向的根目录是 public 目录，要想用户能通过浏览器访问 storage 目录下的文件，需要创建一个软链接，Laravel 内置了这个命令
解决：

>  php artisan storage:link

前端图片显示：
app/Models/Product.php

```php
use Illuminate\Support\Str;
.
.
.
    public function getImageUrlAttribute()
    {
        // 如果 image 字段本身就已经是完整的 url 就直接返回
        if (Str::startsWith($this->attributes['image'], ['http://', 'https://'])) {
            return $this->attributes['image'];
        }
        return \Storage::disk('public')->url($this->attributes['image']);
    }
```
这里 \Storage::disk('public') 的参数 public 需要和我们在 config/admin.php 里面的 upload.disk 配置一致。

然后修改模板文件，改为输出我们刚刚加上的访问器：
resources/views/products/index.blade.php

> <div class="img"><img src="{{ $product->image_url }}" alt=""></div>

Laravel 的模型访问器会自动把下划线改为驼峰，所以 `image_url` 对应的就是 `getImageUrlAttribute`

最后使用factory 工具批量生成模拟数据。

### 收藏商品

#### 数据结构

: 收藏商品本质上是用户和商品的多对多关联，因此不需要创建新的模型，只需要增加一个中间表即可

> php artisan make:migration create_user_favorite_products_table --create=user_favorite_products

#### 字段

user_favorite_products： 

字段  | 描述  | 类型 | 是否索引
--- | --- | --- | ---
id     | 自增长ID | unsigned int | 主键 |
user_id      | 所属用户	        |  unsigned int  | 外键 |
product_id | 所属商品       |    unsigned int | 外键 |

增加模型关联：
app/Models/User.php

```php
    public function favoriteProducts()
    {
        return $this->belongsToMany(Product::class, 'user_favorite_products')
            ->withTimestamps()
            ->orderBy('user_favorite_products.created_at', 'desc');
    }
```

收藏商品逻辑：
```php
 public function favor(Product $product, Request $request)
    {
        $user = $request->user();
        if ($user->favoriteProducts()->find($product->id)) {
            return [];
        }

        $user->favoriteProducts()->attach($product);

        return [];
    }
```

注意：
1.attach() 方法的参数可以是模型的 id，也可以是模型对象本身，因此这里还可以写成 attach($product->id)
2.detach() 方法用于取消多对多的关联，接受的参数个数与 attach() 方法一致


## 购物车

### 数据结构

我们把购物车中的数据存入 `cart_items` 表，表结构如下：

字段  | 描述  | 类型 | 是否索引
--- | --- | --- | ---
id     | 自增长ID | unsigned int | 主键 |
user_id      | 所属用户	        |  unsigned int  | 外键 |
product_sku_id | 商品sku id      |    unsigned int | 外键 |
amount | 商品数量       |    unsigned int | 无 |

### 模型

1.创建模型

> php artisan make:model Models/CartItem -m

2.添加模型关联

app/Models/CartItem.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    .
    .
    .

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function productSku()
    {
        return $this->belongsTo(ProductSku::class);
    }
}
```

app/Models/User.php
```php
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }
```

## 订单数据模型

### 字段分析

由于我们的一笔订单支持多个商品 SKU，因此我们需要 `orders` 和 `order_items` 两张表，orders 保存用户、金额、收货地址等信息，order_items 则保存商品 SKU ID、数量以及与 orders 表的关联

### 数据字段

orders表：

字段  | 描述  | 类型 | 是否索引
--- | --- | --- | ---
id     | 自增长ID | unsigned int | 主键 |
user_id     | 订单流水号        |  varchar | 唯一 |
address | JSON格式的收货地址    |    text| 无 |
total_amount | 订单总金额       |    decimal | 无 |
remark | 订单备注       |    text | 无 |
paid_at | 支付时间      |    datetime, null | 无 |
payment_method | 支付方式       |    varchar, null | 无 |
payment_no | 支付平台订单号      |    varchar, null | 无 |
refund_status | 退款状态     |    varchar | 无 |
refund_no | 退款单号      |    varchar, null | 唯一 |
closed | 订单是否已关闭       |    tinyint, default 0 | 无 |
reviewed | 订单是否已评价       |    tinyint, default 0 | 无 |
ship_status | 物流状态       |    varchar | 无 |
ship_data | 物流数据       |    text, null | 无 |
extra | 其他额外的数据       |    text, null | 无 |

order_items：

字段  | 描述  | 类型 | 是否索引
--- | --- | --- | ---
id     | 自增长ID | unsigned int | 主键 |
order_id     | 所属订单ID        |  unsigned int | 外键|
product_id | 对应商品ID       |    unsigned int | 外键|
product_sku_id | 对应商品SKU ID      |   unsigned int | 外键 |
amount |数量     |    unsigned int | 无 |
price	 | 单价       |    decimal | 无 |
rating | 用户打分      |    unsigned int | 无 |
review | 用户评价     |    text | 无 |
reviewed_at | 评价时间      |    unsigned int | 无 |

### 创建模型

> php artisan make:model Models/Order -mf
> php artisan make:model Models/OrderItem -mf

添加关联：
app/Models/Order.php

```php
     public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
```

app/Models/OrderItem.php

```php
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
```

### 下单减库存

减库存不能简单地通过 update(['stock' => $sku->stock - $amount]) 来操作，在高并发的情况下会有问题，这就需要通过数据库的方式来解决

在 ProductSku 模型里新增两个方法：
app/Models/ProductSku.php

```php
use App\Exceptions\InternalException;
    .
    .
    .
    public function decreaseStock($amount)
    {
        if ($amount < 0) {
            throw new InternalException('减库存不可小于0');
        }

        return $this->newQuery()->where('id', $this->id)->where('stock', '>=', $amount)->decrement('stock', $amount);
    }

    public function addStock($amount)
    {
        if ($amount < 0) {
            throw new InternalException('加库存不可小于0');
        }
        $this->increment('stock', $amount);
    }
```
注意：在订单下单过程中需要使用 `Mysql`事务处理 `DB::transaction()` 保证数据的准确性

### 关闭未支付订单

#### 需求分析

1.需求：
  在创建订单的同时我们减去了对应商品 SKU 的库存，恶意用户可以通过下大量的订单又不支付来占用商品库存，让正常的用户因为库存不足而无法下单。因此我们需要有一个关闭未支付订单的机制，当创建订单之后一定时间内没有支付，将关闭订单并退回减去的库存

2.解决方法：
  可以用 Laravel 提供的 `延迟任务`（Delayed Job）功能来解决。当我们的系统触发了一个延迟任务时，Laravel 会用当前时间加上任务的延迟时间计算出任务应该被执行的时间戳，然后将这个时间戳和任务信息序列化之后存入队列，Laravel 的队列处理器会不断查询并执行队列中满足预计执行时间等于或早于当前时间的任务

#### 创建任务

通过命令 `make:job` 来创建一个任务：

> php artisan make:job CloseOrder

创建的任务类保存在 app/Jobs 目录下，现在编辑刚刚创建的任务类，实现 `ShouldQueue` 说明是 `异步队列`
```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Order;

// 代表这个类需要被放到队列中执行，而不是触发时立即执行
class CloseOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    public function __construct(Order $order, $delay)
    {
        $this->order = $order;
        // 设置延迟的时间，delay() 方法的参数代表多少秒之后执行
        $this->delay($delay);
    }

    // 定义这个任务类具体的执行逻辑
    // 当队列处理器从队列中取出任务时，会调用 handle() 方法
    public function handle()
    {
        // 判断对应的订单是否已经被支付
        // 如果已经支付则不需要关闭订单，直接退出
        if ($this->order->paid_at) {
            return;
        }
        // 通过事务执行 sql
        \DB::transaction(function() {
            // 将订单的 closed 字段标记为 true，即关闭订单
            $this->order->update(['closed' => true]);
            // 循环遍历订单中的商品 SKU，将订单中的数量加回到 SKU 的库存中去
            foreach ($this->order->items as $item) {
                $item->productSku->addStock($item->amount);
            }
        });
    }
}
```

### 触发任务

接下来我们需要在创建订单之后触发这个任务：
app/Http/Controllers/OrdersController.php

```php
use App\Jobs\CloseOrder;
    .
    .
    .
    public function store(Request $request)
    {
        .
        .
        .
        $this->dispatch(new CloseOrder($order, config('app.order_ttl'))); // app.order_ttl 是设置延迟的时间

        return $order;
    }
```

修改配置

默认情况下，Laravel 生成的 .env 文件里把队列的驱动设置成了 `sync`（同步），在同步模式下延迟任务会被立即执行，所以需要先把队列的驱动改成 `redis`

### 查看订单

安全起见我们只允许订单的创建者可以看到对应的订单信息，这个需求可以通过授权策略类（Policy）来实现。
通过 `make:policy` 命令创建一个授权策略类：

> php artisan make:policy OrderPolicy

用法参考用户地址修改策略

### 代码封装

采用 Service 模式来封装代码。购物车的逻辑，放置于 CartService 类里，将下单的业务逻辑代码放置于 OrderService 里

#### 购物车

创建一个 CartService 类：
> mkdir -p app/Services && touch app/Services/CartService.php

app/Services/CartService.php

```php
<?php

namespace App\Services;

use Auth;
use App\Models\CartItem;

class CartService
{
    public function get()
    {
        return Auth::user()->cartItems()->with(['productSku.product'])->get();
    }

    public function add($skuId, $amount)
    {
        $user = Auth::user();
        // 从数据库中查询该商品是否已经在购物车中
        if ($item = $user->cartItems()->where('product_sku_id', $skuId)->first()) {
            // 如果存在则直接叠加商品数量
            $item->update([
                'amount' => $item->amount + $amount,
            ]);
        } else {
            // 否则创建一个新的购物车记录
            $item = new CartItem(['amount' => $amount]);
            $item->user()->associate($user);
            $item->productSku()->associate($skuId);
            $item->save();
        }

        return $item;
    }

    public function remove($skuIds)
    {
        // 可以传单个 ID，也可以传 ID 数组
        if (!is_array($skuIds)) {
            $skuIds = [$skuIds];
        }
        Auth::user()->cartItems()->whereIn('product_sku_id', $skuIds)->delete();
    }
}
```

在 `CartController.php` 中，把需要用到以上逻辑的代码通过 `CartService` 来实现

#### 订单

创建 OrderService 类
> touch app/Services/OrderService.php

app/Services/OrderService.php

```php
<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserAddress;
use App\Models\Order;
use App\Models\ProductSku;
use App\Exceptions\InvalidRequestException;
use App\Jobs\CloseOrder;
use Carbon\Carbon;

class OrderService
{
    public function store(User $user, UserAddress $address, $remark, $items)
    {
        // 开启一个数据库事务
        $order = \DB::transaction(function () use ($user, $address, $remark, $items) {
            // 更新此地址的最后使用时间
            $address->update(['last_used_at' => Carbon::now()]);
            // 创建一个订单
            $order   = new Order([
                'address'      => [ // 将地址信息放入订单中
                    'address'       => $address->full_address,
                    'zip'           => $address->zip,
                    'contact_name'  => $address->contact_name,
                    'contact_phone' => $address->contact_phone,
                ],
                'remark'       => $remark,
                'total_amount' => 0,
            ]);
            // 订单关联到当前用户
            $order->user()->associate($user);
            // 写入数据库
            $order->save();

            $totalAmount = 0;
            // 遍历用户提交的 SKU
            foreach ($items as $data) {
                $sku  = ProductSku::find($data['sku_id']);
                // 创建一个 OrderItem 并直接与当前订单关联
                $item = $order->items()->make([
                    'amount' => $data['amount'],
                    'price'  => $sku->price,
                ]);
                $item->product()->associate($sku->product_id);
                $item->productSku()->associate($sku);
                $item->save();
                $totalAmount += $sku->price * $data['amount'];
                if ($sku->decreaseStock($data['amount']) <= 0) {
                    throw new InvalidRequestException('该商品库存不足');
                }
            }
            // 更新订单总金额
            $order->update(['total_amount' => $totalAmount]);

            // 将下单的商品从购物车中移除
            $skuIds = collect($items)->pluck('sku_id')->all();
            app(CartService::class)->remove($skuIds);

            return $order;
        });

        // 这里我们直接使用 dispatch 函数
        dispatch(new CloseOrder($order, config('app.order_ttl')));

        return $order;
    }
}
```

**关于 Service 模式**

Service 模式将 PHP 的商业逻辑写在对应责任的 Service 类里，解決 Controller 臃肿的问题。并且符合 SOLID 的单一责任原则，购物车的逻辑由 CartService 负责，而不是 CartController ，控制器是调度中心，编码逻辑更加清晰。后面如果我们有 API 或者其他会使用到购物车功能的需求，也可以直接使用 CartService ，代码可复用性大大增加。再加上 Service 可以利用 Laravel 提供的依赖注入机制，大大提高了 Service 部分代码的可测试性，程序的健壮性越佳

## 订单支付

### 安装扩展包

> composer require yansongda/pay

### 支付接口参数配置

config/pay.php

```php
<?php

return [
    'alipay' => [
        'app_id'         => '',
        'ali_public_key' => '',
        'private_key'    => '',
        'log'            => [
            'file' => storage_path('logs/alipay.log'),
        ],
    ],

    'wechat' => [
        'app_id'      => '',
        'mch_id'      => '',
        'key'         => '',
        'cert_client' => '',
        'cert_key'    => '',
        'log'         => [
            'file' => storage_path('logs/wechat_pay.log'),
        ],
    ],
];
```

### 容器

把支付操作类实例注入到容器中，在以后的逻辑中可通过 `app('alipay')` 和 `app('wechat')` 获取实例

AppServiceProvider 的 register() 方法中往容器中注入实例：

```php
use Monolog\Logger;
use Yansongda\Pay\Pay;
.
.
.
    public function register()
    {
        // 往服务容器中注入一个名为 alipay 的单例对象
        $this->app->singleton('alipay', function () {
            $config = config('pay.alipay');
            // 判断当前项目运行环境是否为线上环境
            if (app()->environment() !== 'production') {
                $config['mode']         = 'dev';
                $config['log']['level'] = Logger::DEBUG;
            } else {
                $config['log']['level'] = Logger::WARNING;
            }
            // 调用 Yansongda\Pay 来创建一个支付宝支付对象
            return Pay::alipay($config);
        });

        $this->app->singleton('wechat_pay', function () {
            $config = config('pay.wechat');
            if (app()->environment() !== 'production') {
                $config['log']['level'] = Logger::DEBUG;
            } else {
                $config['log']['level'] = Logger::WARNING;
            }
            // 调用 Yansongda\Pay 来创建一个微信支付对象
            return Pay::wechat($config);
        });
    }
```

支付完成后通过验证支付网关回调的参数判断订单是否成功，修改订单的支付状态

### 生成二维码

通过 composer 引入 `endroid/qr-code` 这个库, 将支付 URL 转成二维码

使用：
```php
use Endroid\QrCode\QrCode;
.
.
.
    public function payByWechat(Order $order, Request $request)
    {
        .
        .
        .
        // 把要转换的字符串作为 QrCode 的构造函数参数
        $qrCode = new QrCode($wechatOrder->code_url);
        .
        .
    }
```

### 支付成功逻辑

如果用户支付成功要发邮件给用户告知订单支付成功， 并且更新商品的购买数量

**1.支付成功事件**

支付成功的事件
> php artisan make:event OrderPaid

触发事件：

> event(new OrderPaid($order));

**2.创建监听器**

希望订单支付之后对应的商品销量会对应地增加，所以创建一个更新商品销量的监听器

> php artisan make:listener UpdateProductSoldCount --event=OrderPaid

app/Listeners/UpdateProductSoldCount.php

```php
<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\OrderItem;
//  implements ShouldQueue 代表此监听器是异步执行的
class UpdateProductSoldCount implements ShouldQueue
{
    // Laravel 会默认执行监听器的 handle 方法，触发的事件会作为 handle 方法的参数
    public function handle(OrderPaid $event)
    {
        // 从事件对象中取出对应的订单
        $order = $event->getOrder();
        // 循环遍历订单的商品
        foreach ($order->items as $item) {
            $product   = $item->product;
            // 计算对应商品的销量
            $soldCount = OrderItem::query()
                ->where('product_id', $product->id)
                ->whereHas('order', function ($query) {
                    $query->whereNotNull('paid_at');  // 关联的订单状态是已支付
                })->sum('amount');
            // 更新商品销量
            $product->update([
                'sold_count' => $soldCount,
            ]);
        }
    }
}
```
在 `EventServiceProvider` 中将事件和监听器关联起来

```php
use App\Events\OrderPaid;
use App\Listeners\UpdateProductSoldCount;
.
.
.
    protected $listen = [
        .
        .
        .
        OrderPaid::class => [
            UpdateProductSoldCount::class,
        ],
    ];
```

## 第三方登录

### 安装扩展包

> composer require overture/laravel-socilate

配置参考 [Github](https://github.com/overtrue/laravel-socialite)

### 使用

1.配置参数文件

config/socialite.php

2.配置路由

```php
Route::get('oauth/{driver}', 'SocialiteController@redirectToProvider')->name('social.login');
Route::get('oauth/{driver}/callback', 'SocialiteController@handleProviderCallback');
```

3.创建控制器

> php artisan make:controller SocialiteController

```php
<?php

namespace App\Http\Controllers;

use Socialite;
use Illuminate\Http\Request;


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
    }
}

```

在用户登录页面添加对应的点击入口

resources/views/auth/login

```html
<div class="form-group">
    <div class="col-md-8 col-md-offset-4">
        <a class="btn btn-link" href="{{ route('social.login', ['driver' => 'google'])}}"><i class="fa fa-google-plus" aria-hidden="true"></i></a>
        <a class="btn btn-link" href="{{ route('social.login', ['driver' => 'wechat'])}}"><i class="fa fa-weixin" aria-hidden="true"></i></a>
        <a class="btn btn-link" href="{{ route('social.login', ['driver' => 'weibo'])}}"><i class="fa fa-weibo" aria-hidden="true"></i></a>
        <a class="btn btn-link" href="{{ route('social.login', ['driver' => 'qq'])}}"><i class="fa fa-qq" aria-hidden="true"></i></a>
        <a class="btn btn-link" href="{{ route('social.login', ['driver' => 'facebook'])}}"><i class="fa fa-facebook-square" aria-hidden="true"></i></a>
        <a class="btn btn-link" href="{{ route('social.login', ['driver' => 'linkedin'])}}"><i class="fa fa-linkedin-square" aria-hidden="true"></i></a>
    </div>
</div>
```

注意： 该扩展包 的微信不支持网页授权登录，只能支持开放平台的扫码登录，facebook 强制使用https

## 优惠券设计

### 字段

order_items：

字段  | 描述  | 类型 | 是否索引
--- | --- | --- | ---
id     | 自增长ID | unsigned int | 主键 |
name     | 优惠券的标题      |  varchar | 无|
code | 优惠码，用户下单时输入     |    varchar| 唯一|
type | 优惠券类型，支持固定金额和百分比折扣      |   varchar | 无 |
value |折扣值，根据不同类型含义不同     |    decimal | 无 |
total	 | 全站可兑换的数量      |    unsigned int | 无 |
used | 当前已兑换的数量      |    unsigned int, default 0 | 无 |
min_amount | 使用该优惠券的最低订单金额   |   decimal | 无 |
not_before | 在这个时间之前不可用    |    datetime, null| 无 |
not_after | 在这个时间之后不可用      |    datetime, null| 无 |
enabled | 评价时间      |    tinyint | 无 |

### 模型

> php artisan make:model Models/CouponCode -mfphp 

在orders 中添加 `coupon_code_id` 字段

关联

app/Models/Order.php

```php
    public function couponCode()
    {
        return $this->belongsTo(CouponCode::class);
    }
```

### 生成测试优惠券数据

优惠券的工厂文件来实现测试时优惠券的生成

database/factories/CouponCodeFactory.php

```php
<?php

use Faker\Generator as Faker;

$factory->define(App\Models\CouponCode::class, function (Faker $faker) {
    // 首先随机取得一个类型
    $type  = $faker->randomElement(array_keys(App\Models\CouponCode::$typeMap));
    // 根据取得的类型生成对应折扣
    $value = $type === App\Models\CouponCode::TYPE_FIXED ? random_int(1, 200) : random_int(1, 50);

    // 如果是固定金额，则最低订单金额必须要比优惠金额高 0.01 元
    if ($type === App\Models\CouponCode::TYPE_FIXED) {
        $minAmount = $value + 0.01;
    } else {
        // 如果是百分比折扣，有 50% 概率不需要最低订单金额
        if (random_int(0, 100) < 50) {
            $minAmount = 0;
        } else {
            $minAmount = random_int(100, 1000);
        }
    }

    return [
        'name'       => join(' ', $faker->words), // 随机生成名称
        'code'       => App\Models\CouponCode::findAvailableCode(), // 调用优惠码生成方法
        'type'       => $type,
        'value'      => $value,
        'total'      => 1000,
        'used'       => 0,
        'min_amount' => $minAmount,
        'not_before' => null,
        'not_after'  => null,
        'enabled'    => true,
    ];
});
```

> php artisan tinker
> factory(App\Models\CouponCode::class, 10)->create()

注意： 在用户下单过程中如果有填写优惠券，检查优惠券是是否存在， 是否已经被试用，是否有效



## 项目总结

该项目主要包含了一下的内容：

* 使用 Laravel 创建新项目；
* composer autoload 功能的使用姿势；
* Laravel Mix 的基本用法；
* 使用 Laravel 快速构建用户登录、注册功能；
* 使用 MailHog 调试邮件发送功能；
* 使用 Laravel 的 Notification 模块发送邮件；
* 优雅地处理 Laravel 项目中的异常；
* 事件与监听器的使用；
* 使用授权策略来控制权限；
* 使用 overtrue/laravel-lang 来汉化错误信息；
* laravel-admin 扩展包的安装与配置；
* 使用 laravel-admin 快速构建对模型的增删改查功能；
* 使用 laravel-admin 配置后台用户角色、权限；
* 商品 SKU 的概念；
* 使用查询构造器根据用户输入来动态构建查询 SQL；
* 设置 Laravel 路由顺序的正确姿势；
* 购物车的设计与实现；
* 使用闭包来校验用户输入；
* 订单流水号的生成；
* 创建订单时保存用户收货地址信息的正确姿势；
* 代表状态的值应使用常量；
* 在 Laravel 中使用数据库事务的正确姿势；
* 高并发下减商品库存的正确姿势；
* 延迟任务的使用；
* 使用预加载与延迟预加载解决数据库 N + 1 问题；
* 通过对业务代码的封装来提高代码的复用性；
* yansongda/pay 扩展包的安装与配置；
* 支付宝沙箱账号的申请与配置；
* 微信扫码支付的开通与配置；
* 拉起支付宝、微信支付；
* 支付的前端回调与后端回调的概念；
* 在本地开发环境处理支付宝、微信支付后端回调的正确姿势；
* 二维码的生成；
* 管理员收到支付订单开始发货；
* 用户收到商品后确认收货；
* 用户确认收货后选择退款；
* 管理员接到退款请求后的拒绝退款；
* 支付宝、微信支付的退款处理；
* 优惠券的设计与实现；
* 社交媒体的登录
* 常见 Web 项目漏洞类型及在 Laravel 项目中的防御措施

项目地址： 
[前台](http://95.169.19.236)
[后台](http://95.169.19.236/admin)