<?php

namespace app\common\validate;

use think\Validate;

/**
 * 登陆-校验
 */
class LoginByCode extends Validate
{
    protected $rule = [
        'phone' => 'require',
        'code' => 'require',
        'wx_code'=>'require',
    ];

    protected $field = [
        'phone' => '手机号',
        'code' => '短信验证码',
        'wx_code'=>'wx.login获取的code'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['phone', 'code', 'wx_code'],
        'edit' => [],

    ];
}
