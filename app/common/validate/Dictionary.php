<?php

namespace app\common\validate;

use think\Validate;

/**
 * 字典-校验
 */
class Dictionary extends Validate
{
    protected $rule = [
        'dictionary_type_uuid' => 'require',
        'tag' => 'require',
        'key' => 'require',
        'order_number' => 'require|number',
        'style' => 'require|in:1,2,3,4,5,6',
        'status' => 'require|in:1,2',
        'note' => 'require',
    ];

    protected $field = [
        'dictionary_type_uuid' => '字段类型uuid',
        'tag' => '标签',
        'key' => '键值',
        'order_number' => '排序序号',
        'style'=>'样式',
        'status'=>'状态',
        'note'=>'备注'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['dictionary_type_uuid','tag','key','order_number','style','status'],
        'setStatus' => ['status'],
    ];


}
