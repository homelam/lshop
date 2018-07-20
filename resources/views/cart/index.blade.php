@extends('layouts.app')

@section('title', '购物车')

@section('content')
<div class="row">
<div class="col-lg-10 col-lg-offset-1">
<div class="panel panel-default">
    <div class="panel-heading">我的购物车</div>
    <div class="panel-body">
    <table class="table table-striped">
        <thead>
        <tr>
            <th><input type="checkbox" id="select-all"></th>
            <th>商品信息</th>
            <th>单价</th>
            <th>数量</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody class="product_list">
        @foreach($cartItems as $item)
        <tr data-id="{{ $item->productSku->id }}">
            <td>
                <input type="checkbox" name="select" value="{{ $item->productSku->id }}" {{ $item->productSku->product->on_sale ? 'checked' : 'disabled' }}>
            </td>
            <td class="product_info">
                <div class="preview">
                    <a target="_blank" href="{{ route('products.show', [$item->productSku->product_id]) }}">
                    <img src="{{ $item->productSku->product->image_url }}">
                    </a>
                </div>
                <div @if(!$item->productSku->product->on_sale) class="not_on_sale" @endif>
                    <span class="product_title">
                        <a target="_blank" href="{{ route('products.show', [$item->productSku->product_id]) }}">{{ $item->productSku->product->name }}</a>
                    </span>
                    <span class="sku_title">{{ $item->productSku->sku }}</span>
                    @if(!$item->productSku->product->on_sale)
                        <span class="warning">该商品已下架</span>
                    @endif
                </div>
            </td>
            <td><span class="price">￥{{ $item->productSku->price }}</span></td>
            <td>
                <input type="text" class="form-control input-sm amount" @if(!$item->productSku->product->on_sale) disabled @endif name="amount" value="{{ $item->amount }}">
            </td>
            <td>
                <button class="btn btn-xs btn-danger btn-remove">移除</button>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    <!-- 用户收货地址 -->
    <div>
        <form class="form-horizontal" role="form" id="order-form">
            <div class="form-group">
                <label class="control-label col-sm-3">选择收货地址</label>
                <div class="col-sm-9 col-md-7">
                    <select class="form-control" name="address">
                        @foreach($addresses as $address)
                            <option value="{{ $address->id }}">{{ $address->full_address }} {{ $address->contact_name }} {{ $address->contact_phone }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3">备注</label>
                <div class="col-sm-9 col-md-7">
                    <textarea name="remark" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <!-- 优惠券开始 -->
            <div class="form-group">
                <label  class="control-label col-sm-3">优惠券</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control" name="coupon_code">
                    <span class="help-block" id="coupon_desc"></span>
                </div>
                <div class="col-sm-3">
                    <button type="button" class="btn btn-success" id="btn-check-btn">检查</button>
                    <button type="button" class="btn btn-danger" style="display: none;" id="btn-cancel-coupon">取消</button>
                </div>
            </div>
            <!-- 优惠券结束 -->
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-3">
                    <button type="button" class="btn btn-primary btn-create-order">提交订单</button>
                </div>
            </div>
        </form>
    </div>
    <!-- 用户收货地址结束 -->
    </div>
</div>
</div>
</div>
@endsection
@section('scriptsAfterJs')
<script>
    $(document).ready(function() {
        $('.btn-remove').click(function() {
            var id = $(this).closest('tr').data('id');
            swal({
                title: "确认要将该商品移除吗？",
                icon: 'warning',
                buttons: ['取消', '确定'],
                dangerMode: true,
            }).then(function(willDelete) {
                // 如果用户点击确定，willDelete的值会是true, 否则是false
                if (!willDelete) {
                    return;
                }
                axios.delete('/cart/' + id).then(function() {
                    location.reload();
                })
            });
        });
        $('#select-all').change(function() {
            var checked = $(this).prop('checked');
            // 对于已经下架的商品我们不希望对应的勾选框会被选中，因此我们需要加上 :not([disabled]) 这个条件
            $('input[name=select][type=checkbox]:not([disabled])').each(function() {
                // 将其勾选状态设为与目标单选框一致
                $(this).prop('checked', checked);
            });
        });
        
        // 提交订单
        $('.btn-create-order').click(function(event) {
            event.preventDefault();

            // 构造请求参数
            var req = {
                address_id: $('#order-form').find('select[name=address]').val(),
                items: [],
                remark: $('#order-form').find('textarea[name=remark]').val(),
                //增加优惠码的提交
                coupon_code: $('input[name=coupon_code]').val(),
            };
            // 遍历table
            $('table tr[data-id]').each(function() {
                var checkbox = $(this).find('input[name=select][type=checkbox]');
                if (checkbox.prop('disabled') || !checkbox.prop('checked')) {
                    return;
                }
                // 获取当前行汇总的输入框
                var amount = $(this).find('input[name=amount]').val();
                // 如果用户将数量设置为0获取不是一个大于0的数字，也跳过
                var reg = /^[1-9]\d*$/;
                if (reg.test(amount)) {
                    req.items.push({
                        sku_id: $(this).data('id'),
                        amount: amount
                    });
                } else {
                    return;
                }
            });
            // 发送post请求
            axios.post("{{ route('orders.store') }}", req).then(function(response) {
                swal('订单提交成功', '', 'success').then(function(){
                    location.href = '/orders/' + response.data.id;
                });
            }, function(error) {
                if (error.response.status === 422) {
                    // http 状态码为 422 代表用户输入校验失败
                    var html = '<div>';
                    _.each(error.response.data.errors, function (errors) {
                        _.each(errors, function (error) {
                            html += error+'<br>';
                        })
                    });
                    html += '</div>';
                    swal({content: $(html)[0], icon: 'error'})
                } else if (error.response.status === 403) {
                    // NOTE: 订单金额不满足优惠券最低金额
                    swal(error.response.data.msg, '', 'error');
                } else {
                    // 其他情况应该是系统挂了
                    swal('系统错误', '', 'error');
                }
            });
        });

        // 检查优惠码
        $('#btn-check-btn').click(function() {
            // 获取用户输入的优惠码
            var code = $('input[name=coupon_code]').val();
            // 如果没有输入则弹框提示
            if (!code) {
                swal('请输入优惠码', '', 'warning');
                return;
            }
            // 检测优惠码是否存在
            axios.get('/coupon_codes/' + encodeURIComponent(code)).then(function(response) {
                // console.log(response.data);
                $('#coupon_desc').text(response.data.description);
                $('input[name=coupon_code]').prop('readonly', true); // 禁用输入框
                $('#btn-cancel-coupon').show(); // 显示 取消 按钮
                $('#btn-check-coupon').hide(); // 隐藏 检查 按钮
            }, function(error) {
                // 如果返回码是404,说明优惠码不存在或暂不可用
                if (error.response.status == 404) {
                    swal('优惠码不存在或暂不可用', '', 'error');
                } else if (error.response.status == 403) {
                    swal(error.response.data.msg, '', 'error');
                } else {
                    swal('系统错误，请稍候重试', '', 'error');
                }
            });
        });

        // 隐藏 按钮点击事件
        $('#btn-cancel-coupon').click(function () {
            $('#coupon_desc').text(''); // 隐藏优惠信息
            $('input[name=coupon_code]').prop('readonly', false);  // 启用输入框
            $('#btn-cancel-coupon').hide(); // 隐藏 取消 按钮
            $('#btn-check-coupon').show(); // 显示 检查 按钮
        });
    });
</script>
@endsection