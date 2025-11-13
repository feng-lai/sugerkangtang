<?php

namespace app\common\validate;

use think\Validate;

/**
 * 单页-校验
 * User: Yacon
 * Date: 2022-08-12
 * Time: 09:16
 */
class Siglepage extends Validate
{
  protected $rule = [
    'content' => 'require',
  ];

  protected $field = [
    'content' => '内容',
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => [],
    'edit' => [
      'content'
    ]
  ];
}
