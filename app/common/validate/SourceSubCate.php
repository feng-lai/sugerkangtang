<?php

namespace app\common\validate;

use think\Validate;

/**
 * 素材二级分类-校验
 * User:
 * Date:
 * Time: 13:25
 */
class SourceSubCate extends Validate
{
  protected $rule = [
    'name' => 'require',
    'source_cate_uuid'=>'require'
  ];

  protected $field = [
    'name' => '名称',
    'source_cate_uuid' => '一级分类',
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => ['name','source_cate_uuid'],
    'edit' => [],
  ];
}
