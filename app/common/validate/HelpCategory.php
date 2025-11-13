<?php

namespace app\common\validate;

use think\Validate;

/**
 * 帮助手册-分类-校验
 */
class HelpCategory extends Validate
{
    protected $rule = [
        'name' => 'require',
    ];

    protected $field = [
        'name'=>'名称',
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['name']
    ];


}
