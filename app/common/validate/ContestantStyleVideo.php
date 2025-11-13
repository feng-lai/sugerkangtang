<?php

namespace app\common\validate;

use think\Validate;

/**
 * 亚姐值视频-校验
 * User:
 * Date:
 * Time:
 */
class ContestantStyleVideo extends Validate
{
  protected $rule = [
    'contestant_style_uuid' => 'require',
    'img' => 'require',
    'video' => 'require',
  ];

  protected $field = [
    'contestant_style_uuid' => '亚姐值',
    'img' => '封面图',
    'video' => '视频',
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => ['contestant_style_uuid', 'video','img'],
    'edit' => [],
  ];
}
