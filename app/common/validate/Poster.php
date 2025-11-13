<?php

namespace app\common\validate;

use think\Validate;

/**
 * 首页弹窗海报管理-校验
 */
class Poster extends Validate
{
    protected $rule = [
        'name' => 'require',
        'img' => 'require',
        'status' => 'require|in:1,2',
    ];

    protected $field = [
        'name' => '名称',
        'img' => '图片',
        'status' => '状态',
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['name', 'img', 'status'],
        'setStatus' => ['status'],
    ];

}
