<?php

namespace app\common\validate;

use think\Validate;

/**
 * 市场人员-校验
 * User:
 * Date: 2022-07-20
 * Time: 13:25
 */
class MarketUser extends Validate
{
  protected $rule = [
    'mobile' => 'require',
    'code' => 'require',
    'password' => 'require',
    'type'=>'number|require|checkType|in:1,2',
    'name'=>'require',
    'company_name'=>'require',
    'role'=>'require|in:1,2,3,4,5,6,7,8,9',
    'recommend'=>'require',
    'img'=>'require'
  ];

  protected $field = [
    'code' => '验证码',
    'mobile' => '账号',
    'password' => '密码',
    'type'=>'登录方式',
    'recommend'=>'推荐人',
    'img'=>'头像'
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => ['code', 'mobile','password'],
    'update' => ['name','company_name','role'],
    'login'=> ['mobile','type'],
    'cms_save'=> ['name','company_name','role','mobile','recommend','img'],
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
