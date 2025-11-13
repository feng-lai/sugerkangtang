<?php

namespace app\common\validate;

use think\Validate;

/**
 * 根据卡号获取银行信息-校验
 */
class GetBankByNum extends Validate
{
  protected $rule = [
    'number' => 'require',
  ];

  protected $field = [
    'number' => '银行卡号',
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => ['number'],
    'edit' => [],
  ];
}
