<?php

namespace app\common\validate;

use think\Validate;

/**
 * 常见问题-校验
 * User: Yacon
 * Date: 2022-07-20
 * Time: 13:25
 */
class Problem extends Validate
{
  protected $rule = [
    'title' => 'require',
    'content' => 'require',
  ];

  protected $field = [
    'title' => '标题',
    'content' => '内容',
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => ['title', 'content'],
    'edit' => [],
  ];
}
