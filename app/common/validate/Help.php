<?php

namespace app\common\validate;

use think\Validate;

/**
 * 帮助手册-校验
 */
class Help extends Validate
{
    protected $rule = [
        'name' => 'require',
        'status' => 'require|in:1,2',
        'help_category_uuid'=>'require',
        'desc'=>'require',
    ];

    protected $field = [
        'status' => '状态',
        'name'=>'问题名称',
        'help_category_uuid'=>'帮助手册分类',
        'desc'=>'详情'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['name', 'help_category_uuid','desc'],
        'setStatus' => ['status'],
    ];


}
