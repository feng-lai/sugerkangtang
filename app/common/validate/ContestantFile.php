<?php

namespace app\common\validate;

use think\Validate;

/**
 * 参赛瞬间-校验
 * User:
 * Date: 2022-07-20
 * Time: 13:25
 */
class ContestantFile extends Validate
{
  protected $rule = [
    'title' => 'require|max:15',
    'img' => 'require',
    'type' => 'require|checkType',
  ];

  protected $field = [
    'title' => '标题',
    'img' => '图片/封面图',
    'type' => '类型',
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => ['title', 'type','img'],
    'edit' => [],
  ];
  // 自定义验证规则
  protected function checkType($value,$rule,$data)
  {
    if($value == 2 && !$data['video']){
      return '视频不能为空';
    }
    return true;
  }
}
