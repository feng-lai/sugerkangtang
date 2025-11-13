<?php

namespace app\common\validate;

use think\Validate;

/**
 * 课程分类-校验
 */
class CourseCate extends Validate
{
  protected $rule = [
      'name' => 'require',
      'weight'=>'require'
  ];

  protected $field = [
      'name' => '名称',
      'weight' => '权重'
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => ['name', 'weight'],
    'update' => ['name', 'weight'],
  ];
}
