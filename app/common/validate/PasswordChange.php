<?php

namespace app\common\validate;

use think\Validate;

/**
 * 修改密码-校验
 * User:
 * Date: 2022-07-20
 * Time: 13:25
 */
class PasswordChange extends Validate
{
  protected $rule = [
    'mobile' => 'require',
    'code' => 'require',
    'password'=>'require'
  ];

  protected $field = [
    'code' => '验证码',
    'mobile' => '账号',
    'password' => '密码',
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => [],
    'update' => ['code','mobile','password'],
    'login'=> ['mobile','type'],
  ];
  // 自定义验证规则
  protected function checkType($value,$rule,$data)
  {
    if($value == 1){
      if(!$data['password']){
        return '密码不能为空';
      }
    }
    if($value == 2){
      if(!$data['code']){
        return '验证码不能为空';
      }
    }
    return true;
  }
}
