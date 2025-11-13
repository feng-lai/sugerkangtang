<?php

namespace app\common\validate;

use think\Validate;

/**
 * 选手-校验
 * User: Yacon
 * Date: 2022-07-20
 * Time: 13:25
 */
class Contestant extends Validate
{
  protected $rule = [
    'level' => 'require',
  ];

  protected $field = [
    'level' => '权重',
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => [],
    'update' => [],
  ];
}
