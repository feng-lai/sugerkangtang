<?php

namespace app\common\validate;

use think\Validate;

/**
 * app版本-校验
 * User: Yacon
 * Date: 2022-07-20
 * Time: 13:25
 */
class ApkVersion extends Validate
{
  protected $rule = [
    'v' => 'require',
    'upgrade'=>'require'
  ];

  protected $field = [
    'v' => '版本号',
    'upgrade'=>'是否强制更新'
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => ['v','upgrade'],
    'edit' => [],
  ];
}
