<?php

namespace app\common\validate;

use think\Validate;

/**
 * 账单-校验
 * User: Yacon
 * Date: 2022-07-20
 * Time: 13:25
 */
class Bill extends Validate
{
  protected $rule = [

  ];

  protected $field = [

  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => [],
    'edit' => [],
  ];
}
