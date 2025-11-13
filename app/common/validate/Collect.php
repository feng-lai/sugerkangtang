<?php

namespace app\common\validate;

use think\Validate;

/**
 * 收藏-校验
 */
class Collect extends Validate
{
  protected $rule = [
    'product_uuid'=>'require'
  ];

  protected $field = [
    'product_uuid'=>'商品'
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => ['product_uuid'],
    'edit' => [],
  ];
}
