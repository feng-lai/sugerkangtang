<?php

namespace app\common\validate;

use think\Validate;

/**
 * Class Score
 * @package app\common\validate
 */
class Score extends Validate
{
  protected $rule = [
    'user_uuid' => 'require',
    'score' => 'require',
  ];

  protected $field = [
    'user_uuid' => '用户uuid',
    'score' => '积分',
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => ['user_uuid', 'score'],
    'edit' => [],
  ];
}

