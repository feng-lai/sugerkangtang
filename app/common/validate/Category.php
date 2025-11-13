<?php

namespace app\common\validate;

use think\Validate;

/**
 * 分类-校验
 */
class Category extends Validate
{
    protected $rule = [
        'name' => 'require',
        'img' => 'require',
        'vis' => 'require|in:1,2',
    ];

    protected $field = [
        'name' => '名称',
        'img' => '图标',
        'vis' => '状态'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['name', 'img', 'vis'],
        'vis'=>['vis'],
        'edit' => [],
    ];
}
