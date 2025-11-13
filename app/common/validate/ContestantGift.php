<?php

namespace app\common\validate;

use think\Validate;

/**
 * 投票送礼-校验
 * User: Yacon
 * Date: 2022-07-20
 * Time: 13:25
 */
class ContestantGift extends Validate
{
  protected $rule = [
    'gift_set_uuid' => 'require',
    'contestant_uuid' => 'require',
    'qty' => 'require',
    'start_time'=>'require',
    'end_time'=>'require',

  ];

  protected $field = [
    'gift_set_uuid' => '礼物配置uuid',
    'contestant_uuid' => '选手uuid',
    'qty' => '数量',
    'start_time' => '开始时间',
    'end_time' => '结束时间',
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => ['gift_set_uuid', 'contestant_uuid','qty'],
    'edit' => [],
    'index'=>['start_time','end_time']
  ];
}
