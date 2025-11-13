<?php

namespace app\common\validate;

use think\Validate;

/**
 * 购物车-校验
 */
class Cart extends Validate
{
    protected $rule = [
        'product_attribute_uuid' => 'require',
        'qty'=>'require|integer',
    ];

    protected $field = [
        'product_attribute_uuid' => '商品-规格uuid',
        'qty'=>'数量'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['product_attribute_uuid','qty'],
        'edit' => [],
        'setQty' => ['qty'],
    ];
}
