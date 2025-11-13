<?php

namespace app\common\validate;

use think\Validate;

/**
 * 体检报告-校验
 */
class MedicalReport extends Validate
{
    protected $rule = [
        'name' => 'require',
        'date' => 'require',
        'ext' => 'require',
        'file' => 'require',
        'ca_file' => 'require',
        'gender' => 'require|in:1,2',
        'status' => 'require',
    ];

    protected $field = [
        'name' => '姓名',
        'date' => '日期',
        'gender'=>'性别',
        'ext' => '额外数据',
        'file' => '文件',
        'ca_file'=>'共识书附件',
        'status' => '状态',
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['name', 'date', 'ext', 'file', 'gender','ca_file'],
        'edit' => [],
        'setStatus' => ['status'],
    ];
}
