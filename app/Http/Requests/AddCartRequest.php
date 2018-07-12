<?php

namespace App\Http\Requests;

use App\Models\ProductSku;

class AddCartRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // 通过匿名函数的方式来校验用户输入
        return [
            'sku_id' => [
                'required', 
                function($attribute, $value, $fail) {
                    if (!$sku = ProductSku::find($value)) {
                        $fail('该商品不存在');
                        return;
                    }
                    if (!$sku->product->on_sale) {
                        $fail('该商品不在销售范围');
                        return;
                    }
                    if ($sku->stock === 0) {
                        $fail('该商品已售罄');
                        return;
                    }
                    if ($this->input('amont') > 0 && $sku->stock < $this->input('amount')) {
                        $fail('该商品库存不足');
                        return;
                    }
                },
            ],
            'amount' => ['required', 'integer', 'min:1'],
        ];
    }

    public function attributes()
    {
        return ['amount' => '商品数量'];
    }

    public function messages()
    {
        return [
            'sku_id.required' => '请选择商品',
            'amount.integer' => '商品数量必须为整数',
            'amount.min' => '商品数量不能小于1'
        ];
    }
}
