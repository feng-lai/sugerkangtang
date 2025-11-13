<?php

namespace app\common\validate;

use think\Validate;

/**
 * 拼课标签-校验
 */
class Tag extends Validate
{
  protected $rule = [
      'name' => 'require',
      'sort'=>'require'
  ];

  protected $field = [
      'name' => '名称',
      'sort' => '序号'
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => ['name'],
    'update' => ['sort'],
  ];
}
