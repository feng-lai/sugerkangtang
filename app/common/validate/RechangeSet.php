<?php

namespace app\common\validate;

use think\Validate;

/**
 * 充值设置-校验
 * User:
 * Date: 2022-07-20
 * Time: 13:25
 */
class RechangeSet extends Validate
{
  protected $rule = [
    //'price' => 'require',
    'coins' => 'require',
    'score' => 'require'
  ];

  protected $field = [
    //'price' => '价格',
    'coins' => '星币',
    'score' => '积分'
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => [ 'coins','score'],
    'edit' => [],
  ];
}
