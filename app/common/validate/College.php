<?php

namespace app\common\validate;

use think\Validate;

/**
 * 书院-校验
 */
class College extends Validate
{
  protected $rule = [
      'name' => 'require',
      'img'=>'require',
      'dsc'=>'require'
  ];

  protected $field = [
      'name' => '名称',
      'img'=>'logo',
      'dsc'=>'简短说明'
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => ['name','img','dsc'],
    'update' => [],
  ];
}
