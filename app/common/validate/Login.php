<?php

namespace app\common\validate;

use think\Validate;

/**
 * 后台登陆-校验
 * User: Yacon
 * Date: 2022-07-20
 * Time: 13:25
 */
class Login extends Validate
{
  protected $rule = [
    'mobile' => 'require',
    'password' => 'require',
  ];

  protected $field = [
    'mobile' => '账号',
    'password' => '密码',
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => ['mobile', 'password'],
    'edit' => [],
  ];
}
