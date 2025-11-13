<?php

namespace app\common\validate;

use think\Validate;

/**
 * 市场人员提现-校验
 * User:
 * Date: 2022-07-20
 * Time: 13:25
 */
class MarketUserCashOut extends Validate
{
  protected $rule = [
    'price'=>'require',
    'type'=>'require|checkType',
    'status'=>'require'
  ];

  protected $field = [
    'price'=>'提现金额',
    'type'=>'提现方式',
    'bank'=>'银行卡号',
    'status'=>'状态'
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => ['price','type','bank'],
    'update' => ['status'],
  ];
  // 自定义验证规则
  protected function checkType($value,$rule,$data)
  {
    if($value == 2 && !$data['bank']){
      return '银行卡号不能为空';
    }
    return true;
  }
}
