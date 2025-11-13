<?php

namespace app\common\validate;

use think\Validate;

/**
 * 小码短链接-校验
 * User:
 * Date: 2022-07-20
 * Time: 13:25
 */
class LinkUrl extends Validate
{
  protected $rule = [
    'uuid' => 'require',
    'origin_url'=>'require',
    'url'=>'require'
  ];

  protected $field = [
    'uuid' => '用户uuid',
    'origin_url' => '跳转链接',
    'url'=>'短链接'
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => ['uuid', 'origin_url'],
    'update' => ['uuid', 'origin_url','url'],
  ];
}
