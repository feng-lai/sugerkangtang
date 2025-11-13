<?php

namespace app\common\validate;

use think\Validate;

/**
 * 素材一级分类-校验
 * User:
 * Date:
 * Time: 13:25
 */
class SourceCate extends Validate
{
  protected $rule = [
    'name' => 'require',
  ];

  protected $field = [
    'name' => '名称',
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => ['name'],
    'edit' => [],
  ];
}
