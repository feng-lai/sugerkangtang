<?php

namespace app\common\validate;

use think\Validate;

/**
 * 关注-校验
 * User: Yacon
 * Date: 2022-07-20
 * Time: 13:25
 */
class Attention extends Validate
{
  protected $rule = [
    'contestant_uuid' => 'require',
    'type' => 'require',
  ];

  protected $field = [
    'contestant_uuid' => '选手uuid',
    'type' => '关注/取消关注',
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => ['contestant_uuid', 'type'],
    'edit' => [],
  ];
}
