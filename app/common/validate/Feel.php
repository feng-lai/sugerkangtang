<?php

namespace app\common\validate;

use think\Validate;

/**
 * 心得-校验
 */
class Feel extends Validate
{
    protected $rule = [
        'order_uuid' => 'require',
        'anonymous'=>'require',
        'content'=>'require',
    ];

    protected $field = [
        'order_uuid' => '拼团课程订单uuid',
        'anonymous'=>'是否匿名(1=是 2=否)',
        'content'=>'内容',
    ];

    protected $message = [];

    protected $scene = [
        'save' => ['order_uuid','anonymous','content'],
    ];
}
