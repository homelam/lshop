@extends('layouts.app')
@section('title', $product->title)

@section('content')
<div class="row">
    <div class="col-lg-10 col-lg-offset-1">
        <div class="panel panel-default">
            <div class="panel-body product-info">
                <div class="row">
                    <div class="col-sm-5 img">
                        <img class="cover" src="{{ $product->image_url }}" alt="">
                    </div>
                    <div class="col-sm-7">
                        <div class="title">{{ $product->name }}</div>
                        <div class="price"><label>价格</label><em>￥</em><span>{{ $product->price }}</span></div>
                        <div class="sales_and_reviews">
                            <div class="sold_count">累计销量 <span class="count">{{ $product->sold_count }}</span></div>
                            <div class="review_count">累计评价 <span class="count">{{ $product->review_count }}</span></div>
                            <div class="rating" title="评分 {{ $product->rating }}">评分 <span class="count">{{ str_repeat('★', floor($product->rating)) }}{{ str_repeat('☆', 5 - floor($product->rating)) }}</span></div>
                        </div>
                        <div class="skus">
                            <label>选择</label>
                            <div class="btn-group" data-toggle="buttons">
                                @foreach($product->skus as $sku)
                                <label class="btn btn-default sku-btn" data-picture="{{ $sku->picture ? getImageUrlAttribute($sku->picture) : $product->image_url }}" data-price="{{ $sku->price}}" data-stock="{{ $sku->stock }}" data-toggle="tooltip" title="{{ $sku->description }}" data-placement="bottom">
                                <input type="radio" name="skus" autocomplete="off" value="{{ $sku->id }}"> {{ $sku->sku }}
                                </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="cart_amount"><label>数量</label><input type="text" class="form-control input-sm" value="1"><span>件</span><span class="stock"></span></div>
                        <div class="buttons">
                            @if ($favored)
                            <button class="btn btn-danger btn-disfavor">取消收藏</button>
                            @else
                            <button class="btn btn-success btn-favor">❤ 收藏</button>
                            @endif
                            <button class="btn btn-primary btn-add-to-cart">加入购物车</button>
                        </div>
                    </div>
                </div>
                <div class="product-detail">
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active"><a href="#product-detail-tab" aria-controls="product-detail-tab" role="tab" data-toggle="tab">商品详情</a></li>
                        <li role="presentation"><a href="#product-reviews-tab" aria-controls="product-reviews-tab" role="tab" data-toggle="tab">用户评价</a></li>
                    </ul>
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" id="product-detail-tab">
                            {!! $product->description !!}
                        </div>
                        <!-- 商品评价 -->
                        <div role="tabpanel" class="tab-pane" id="product-reviews-tab">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <td>用户</td>
                                        <td>商品</td>
                                        <td>评分</td>
                                        <td>评价</td>
                                        <td>评论时间</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reviews as $review)
                                    <tr>
                                        <td>{{ $review->order->user->name }}</td>
                                        <td>{{ $review->productSku->sku }}</td>
                                        <td>{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</td>
                                        <td>{{ $review->review }}</td>
                                        <td>{{ $review->reviewed_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scriptsAfterJs')
<script>
    $(document).ready(function () {
        // $('[data-toggle="tooltip"]').tooltip({trigger: 'hover'});
        $('.sku-btn').click(function () {
            $('.product-info .price span').text($(this).data('price'));
            $('.product-info .stock').text('库存：' + $(this).data('stock') + '件');
            $('.product-info .img').find('img').attr('src', $(this).data('picture'));
        });

        // 监听收藏按钮事件
        $('.btn-favor').click(function () {
            // 发起一个post 的ajax请求
            axios.post("{{ route('products.favor', ['product' => $product->id]) }}").then(function() {
                swal('操作成功', '', 'success').then(function() {
                    location.reload();
                });
            }, function(error) {
                // 401 代表用户还没有登录
                if (error.response && error.response.status === 401) {
                    swal('请先登录', '', 'error');
                } else if (error.response && error.response.data.msg) {
                    // 其他有msg 字段的情况，将msg信息提示给用户
                    swal(error.response.data.msg, '', 'error');
                } else {
                    swal('系统错误', '', 'error');
                }
            });
        });
        
        // 取消收藏
        $('.btn-disfavor').click(function () {
            // 发起一个post 的ajax请求
            axios.delete("{{ route('products.disfavor', ['product' => $product->id]) }}").then(function() {
                swal('操作成功', '', 'success')
                    .then(function () {
                        location.reload();
                });
            });
        });

        // 添加购物车
        $('.btn-add-to-cart').click(function() {
            axios.post("{{ route('cart.add') }}", {
                sku_id : $('label.active input[name=skus]').val(),
                amount: $('.cart_amount input').val(),
            }).then(function() {
                swal('加入购物车成功', '', 'success');
            }, function(error) {
                if (error.response.status === 401) {
                    // http 状态码 401 代表用户尚未登录
                    swal('请先登录', '', 'error');
                } else if (error.response.status === 422) {
                    // http 状态码为 422 代表用户输入校验失败
                    var html = '<div>';
                    _.each(error.response.data.errors, function (errors) {
                        _.each(errors, function (error) {
                            html += error+'<br>';
                        })
                    })
                    html += '</div>';
                    swal({content: $(html)[0], icon: 'error'});
                } else  {
                    swal('系统错误', '', 'error');
                }
            });
        });
    });
</script>
@endsection