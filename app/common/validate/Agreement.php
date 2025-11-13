<?php

namespace app\common\validate;

use think\Validate;

/**
 * 协议中心-校验
 */
class Agreement extends Validate
{
    protected $rule = [
        'type' => 'require|in:1,2,3,4,5',
        'content' => 'require',
        'ver' => 'require'
    ];

    protected $field = [
        'type' => '类型',
        'content' => '协议内容',
        'ver' => '版本'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['type','content','ver']
    ];


}
