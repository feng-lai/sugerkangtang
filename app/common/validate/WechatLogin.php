<?php

namespace app\common\validate;

use think\Validate;

/**
 * 微信登录-校验
 * User: Yacon
 * Date: 2022-02-15
 * Time: 10:36
 */
class WechatLogin extends Validate
{
    protected $rule = [
        'code' => 'require',
        'phone' => 'require',
    ];

    protected $field = [
        'code' => 'code码',
        'phone' => '手机号'
    ];

    protected $message = [];

    protected $scene = [
        'save' => ['code', 'phone'],
    ];
}
