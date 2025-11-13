<?php

namespace app\common\validate;

use think\Validate;

/**
 * 统一下单-校验
 * User: Yacon
 * Date: 2022-02-19
 * Time: 23:41
 */
class UnionOrderPayment extends Validate
{
  protected $rule = [
    'order_uuid' => 'require',
    'type' => 'require',
  ];

  protected $field = [
    'order_uuid' => '订单',
    'type' => '订单类型',
  ];

  protected $message = [];

  protected $scene = [
    'save' => ['order_uuid', 'type'],
  ];
}
