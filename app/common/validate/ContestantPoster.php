<?php

namespace app\common\validate;

use think\Validate;

/**
 * 海报-校验
 * User:
 * Date: 2022-07-20
 * Time: 13:25
 */
class ContestantPoster extends Validate
{
  protected $rule = [
    'contestant_img_uuid'=>'require',
    'status'=>'require',
    'contestant_uuid'=>'require',
    'img'=>'require',
    'type'=>'require'
  ];

  protected $field = [
    'contestant_img_uuid'=>'素材图片uuid',
    'status'=>'状态',
    'contestant_uuid'=>'选手uuid',
    'img'=>'海报图片',
    'type'=>'类型'
  ];

  protected $message = [];

  protected $scene = [
    'index' => ['contestant_uuid'],
    'save' => ['contestant_img_uuid','img'],
    'update' => ['status'],
  ];
}
