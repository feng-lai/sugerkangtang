<?php

namespace app\common\validate;

use think\Validate;

/**
 * 礼物设置-校验
 * User: Yacon
 * Date: 2022-07-20
 * Time: 13:25
 */
class GiftSet extends Validate
{
  protected $rule = [
    'name' => 'require',
    'score' => 'require',
    'persent' => 'require',
    'img' => 'require',
    'coins' => 'require',
  ];

  protected $field = [
    'name' => '名称',
    'score' => '积分',
    'persent' => '返佣比例',
    'img' => '图片',
    'coins' => '星币',
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => ['name', 'score','persent','img','coins'],
    'edit' => [],
  ];
}
