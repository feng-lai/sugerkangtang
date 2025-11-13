<?php

namespace app\common\validate;

use think\Validate;

/**
 * 意见反馈-校验
 * User: Yacon
 * Date: 2022-07-20
 * Time: 13:25
 */
class Feedback extends Validate
{
  protected $rule = [
    'content' => 'require',
    'user_uuid'=>'require'
  ];

  protected $field = [
    'content' => '内容',
    'user_uuid'=>'用户uuid'
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => ['content','user_uuid'],
    'edit' => [],
  ];
}
