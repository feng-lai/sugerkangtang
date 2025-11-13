<?php

namespace app\common\validate;

use think\Validate;

/**
 * 后台菜单-校验
 * User: Yacon
 * Date: 2022-07-20
 * Time: 13:25
 */
class AdminMenu extends Validate
{
    protected $rule = [
        'name' => 'require|checkName',
        'url' => 'require',
        'pid' => '',
        'level' => 'require',
        'type' => 'require',
        'status' => 'require',
        'vis' => 'require'
    ];

    protected $field = [
        'name' => '名称',
        'url' => 'url',
        'type' => '类型',
        'status' => '状态',
        'vis' => '是否显示'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['name', 'url','type','status','vis'],
        'edit' => ['name', 'url','type','status','vis','level'],
    ];
    protected function checkName($value, $rule, $data){
        // 数据库唯一性校验（根据业务需求）
        if (isset($this->scene['edit']) && !empty($data['uuid'])) {
            $exists = db('admin_menu')->where('name', $value)->where('pid',$data['pid'])->where('level',$data['level'])->where('is_deleted',1)->where('uuid', '<>', $data['uuid'])->find();
        } else {
            $exists = db('admin_menu')->where('name', $value)->where('pid',$data['pid'])->where('level',$data['level'])->where('is_deleted',1)->find();
        }
        if ($exists) {
            return '菜单已存在';
        }
        return true;
    }
}
