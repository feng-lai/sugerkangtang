<?php

namespace app\common\validate;

use think\Validate;

/**
 * 登陆-校验
 * User: Yacon
 * Date: 2022-07-20
 * Time: 13:25
 */
class LoginByPassword extends Validate
{
  protected $rule = [
    'user_name' => 'require',
    'password' => 'require',
  ];

  protected $field = [
    'user_name' => '账号',
    'password' => '密码',
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => ['user_name', 'password'],
    'edit' => [],
  ];
}
