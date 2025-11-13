<?php

namespace app\common\validate;

use think\Validate;

/**
 * 后台用户-校验
 * User:
 * Date: 2022-07-20
 * Time: 13:25
 */
class AdminLogin extends Validate
{
    protected $rule = [
        'uname' => 'require',
        'password' => 'require',
        'captcha' => 'require',
        'captcha_id' => 'require',
    ];

    protected $field = [
        'uname' => '账号',
        'password' => '密码',
        'captcha'=>'验证码',
        'captcha_id'=>'生成验证码的id'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['uname', 'password','captcha','captcha_id'],
        'edit' => [],
    ];
}
