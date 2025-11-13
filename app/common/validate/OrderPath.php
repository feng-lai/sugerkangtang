<?php

namespace app\common\validate;

use app\api\model\ProductAttribute;
use think\Validate;

/**
 * 订单物流-校验
 * User:
 * Date: 2022-07-20
 * Time: 13:25
 */
class OrderPath extends Validate
{
    protected $rule = [
        'com' => 'require',
        'num' => 'require',
        'order_id' => 'require',
        'com_name' => 'require',
    ];

    protected $field = [
        'order_id' => '订单号',
        'com' => '快递编码',
        'num' => '快递单号',
        'com_name'=>'快递公司名称'
    ];

    protected $message = [];

    protected $scene = [
        'list' => ['order_id'],
        'save' => ['com', 'num','com_name'],
        'edit' => [],
    ];

}
