<?php

namespace app\common\validate;

use think\Validate;

/**
 * 后台角色-校验
 * User: Yacon
 * Date: 2022-07-20
 * Time: 13:25
 */
class AdminRole extends Validate
{
    protected $rule = [
        'name' => 'require',
        'desc' => 'require',
        'menus' => 'require',
    ];

    protected $field = [
        'name' => '名称',
        'desc' => '描述',
        'menus'=>'菜单'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['name'],
        'edit' => [],
    ];
}
