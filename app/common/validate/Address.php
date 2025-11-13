<?php

namespace app\common\validate;

use think\Validate;

/**
 * 收货地址-校验
 */
class Address extends Validate
{
    protected $rule = [
        'name' => 'require',
        'phone' => 'require',
        'address' => 'require',
        'province' => 'require',
        'city' => 'require',
        'district' => 'require',
        'tag'=>'require',
        'is_defult'=>'require|in:1,2',
        'user_uuid'=>'require',
    ];

    protected $field = [
        'name' => '姓名',
        'phone' => '手机号',
        'address' => '详细地址',
        'province' => '省',
        'city' => '市',
        'district' => '区',
        'tag'=>'标签',
        'is_default'=>'是否默认地址',
        'user_uuid'=>'用户uuid'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['name', 'phone', 'address', 'province', 'city', 'district', 'tag', 'is_default'],
        'cms_save'=>['name', 'phone', 'address', 'province', 'city', 'district', 'tag', 'is_default','user_uuid'],
        'edit' => [],
    ];
}
