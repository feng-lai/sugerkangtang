<?php

namespace app\common\validate;

use think\Validate;

/**
 * 选手海报模板-校验
 * User:
 * Date: 2022-07-20
 * Time: 13:25
 */
class ContestantPosterTemplate extends Validate
{
  protected $rule = [
    'name' => 'require',
    'type' => 'require|in:1,2,3',
    'img' => 'require',
    'link' => 'require|in:1,2',
    'is_ps' => 'require|in:1,2',
  ];

  protected $field = [
    'name' => '名称',
    'type' => '类型',
    'img' => '模板图片',
    'link' => '关联类型',
    'is_ps' => '是否需要抠图',
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => ['name', 'type','img','link','is_ps'],
    'edit' => [],
  ];
}
