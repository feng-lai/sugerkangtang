<?php

namespace app\common\validate;

use think\Validate;

/**
 * 苹果登录-校验
 * User: Yacon
 * Date: 2022-02-15
 * Time: 10:36
 */
class IosLogin extends Validate
{
  protected $rule = [
    'identity_token'=>'require',
    'apple_union_id'=>'require'
  ];

  protected $field = [
    'identity_token' => '苹果登录token',
    'apple_union_id' => '苹果用户id',
  ];

  protected $message = [];

  protected $scene = [
    'save' => ['identity_token','apple_union_id'],
  ];
}
