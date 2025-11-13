<?php

namespace app\common\validate;

use think\Validate;

/**
 * 后台用户-校验
 * User:
 * Date: 2022-07-20
 * Time: 13:25
 */
class ContestantImg extends Validate
{
  protected $rule = [
    'img' => 'require',
  ];

  protected $field = [
    'img' => '图片地址',
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => ['img'],
    'edit' => [],
  ];
}
