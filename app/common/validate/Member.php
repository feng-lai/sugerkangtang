<?php

namespace app\common\validate;

use think\Validate;

/**
 * 会员设置-校验
 * User:
 * Date: 2022-07-20
 * Time: 13:25
 */
class Member extends Validate
{
    protected $rule = [
        'name' => 'require',
        'price' => 'require',
        'img' => 'require',
        'bg' => 'require',
        'text_color' => 'require',
        'pid'=>'require',
    ];
    protected $field = [
        'name' => '名称',
        'price' => '售价',
        'img' => '图标',
        'bg' => '卡片底色',
        'text_color' => '卡片文字颜色',
        'pid'=>'下级会员uuid'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['name', 'price', 'img', 'bg', 'text_color','pid'],
        'edit' => [],
    ];
}
