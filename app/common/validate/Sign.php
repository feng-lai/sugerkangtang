<?php

namespace app\common\validate;

use think\Validate;

/**
 * Class Score
 * @package app\common\validate
 */
class Sign extends Validate
{
    protected $rule = [
        'course_uuid' => 'require',
        'user_uuid' => 'require'
    ];

    protected $field = [
        'course_uuid' => '拼课课程uuid',
        'user_uuid' => '学生uuid'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['course_uuid'],
        'cms_save' => ['course_uuid','user_uuid'],
        'edit' => [],
    ];
}

