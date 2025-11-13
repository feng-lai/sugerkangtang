<?php

namespace app\common\validate;

use think\Validate;

/**
 * 支付-校验
 * User:
 * Date: 2022-07-20
 * Time: 13:25
 */
class Pay extends Validate
{
    protected $rule = [
        'order_id' => 'require'
    ];

    protected $field = [
        'order_id' => '订单号'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['order_id'],
        'edit' => [],
    ];
}
