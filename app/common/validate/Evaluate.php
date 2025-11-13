<?php

namespace app\common\validate;

use think\Validate;

/**
 * 评价-校验
 */
class Evaluate extends Validate
{
    protected $rule = [
        'admin_uuid' => 'require',
        'content' => 'require',
        'point'=>'require',
        'anonymous'=>'require',
        'course_uuid'=>'require'
    ];

    protected $field = [
        'admin_uuid' => '授课教师uuid',
        'content'=>'内容',
        'point'=>'分数(0-5)',
        'anonymous'=>'是否匿名(1=是 2=否)',
        'course_uuid'=>'课程uuid'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['admin_uuid','content','point','anonymous','course_uuid'],
        'edit' => [],
    ];
}
