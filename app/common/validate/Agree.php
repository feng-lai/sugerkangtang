<?php

namespace app\common\validate;

use think\Validate;

/**
 * 点赞-校验
 * User: Yacon
 * Date: 2022-07-20
 * Time: 13:25
 */
class Agree extends Validate
{
  protected $rule = [
    'contestant_uuid' => 'require',
    'type' => 'require',
  ];

  protected $field = [
    'contestant_uuid' => '选手uuid',
    'type' => '点赞/取消点赞',
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => ['contestant_uuid', 'type'],
    'edit' => [],
  ];
}
