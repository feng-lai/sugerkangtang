<?php

namespace app\common\validate;

use think\Validate;

/**
 * 投诉建议-校验
 */
class Complaint extends Validate
{
  protected $rule = [
      'content' => 'require',
      'img'=>'require',
      'anonymous'=>'require|number',
      'type'=>'require|number'
  ];

  protected $field = [
      'content' => '内容',
      'img'=>'封面',
      'anonymous'=>'是否匿名',
      'type'=>'类型'
  ];

  protected $message = [];

  protected $scene = [
    'save' => ['content','type','anonymous']
  ];
}
