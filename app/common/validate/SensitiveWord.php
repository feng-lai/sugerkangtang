<?php

namespace app\common\validate;

use think\Validate;

/**
 * 敏感词-校验
 * User: Yacon
 * Date: 2022-07-20
 * Time: 13:25
 */
class SensitiveWord extends Validate
{
  protected $rule = [
    'name' => 'require',
  ];

  protected $field = [
    'name' => '敏感词',
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => ['name'],
    'edit' => [],
  ];
}
