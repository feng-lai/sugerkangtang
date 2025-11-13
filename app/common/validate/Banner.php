<?php

namespace app\common\validate;

use think\Validate;

/**
 * 轮播-校验
 */
class Banner extends Validate
{
    protected $rule = [
        'name' => 'require',
        'file' => 'require',
        'type' => 'require|in:1,2',
        'order_number' => 'require|integer',
        'link_type' => 'require|number|in:1,2,3',
        'content' => 'require',
        'status' => 'require|in:1,2',
    ];

    protected $field = [
        'order_number' => '序号',
        'file' => '图片/视频',
        'type' => '跳转类型',
        'content' => '跳转内容',
        'link_type' => '跳转链接类型',
        'name'=>'名称',
        'status'=>'状态'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['name', 'file', 'type', 'link_type', 'order_number'],
        'setStatus' => ['status'],
    ];


}
