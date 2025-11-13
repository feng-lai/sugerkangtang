<?php

namespace app\common\validate;

use think\Validate;

/**
 * 订单地址-校验
 */
class OrderAddress extends Validate
{
    protected $rule = [
        'name' => 'require',
        'phone' => 'require',
        'address' => 'require',
        'province' => 'require',
        'city' => 'require',
        'district' => 'require',
        'tag'=>'require',
    ];

    protected $field = [
        'name' => '姓名',
        'phone' => '手机号',
        'address' => '详细地址',
        'province' => '省',
        'city' => '市',
        'district' => '区',
        'tag'=>'标签',
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['name', 'phone', 'address', 'province', 'city', 'district','tag'],
        'edit' => [],
    ];
}
