<?php

namespace app\common\validate;

use think\Validate;

/**
 * 获取手机号码-校验
 * User:
 * Date: 2022-07-20
 * Time: 13:25
 */
class GetMobile extends Validate
{
  protected $rule = [
    'code' => 'require',
  ];

  protected $field = [
    'code' => '手机号获取凭证',
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => ['code'],
    'edit' => [],
  ];
}
