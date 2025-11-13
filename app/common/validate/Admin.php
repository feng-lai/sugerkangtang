<?php

namespace app\common\validate;

use think\Validate;

/**
 * 后台用户-校验
 * User:
 * Date: 2022-07-20
 * Time: 13:25
 */
class Admin extends Validate
{
    protected $rule = [
        'role_uuid' => 'require',
        'name' => 'require',
        'uname' => 'require|checkUname',
        'phone' => 'require|checkPhone',
        'gender' => 'require',
        'admin_uuid'=>'require',
        'reset_password_note'=>'require',
        'set_status_note'=>'require',
        'status'=>'require',
        'password'=>'require',
        'old_password'=>'require',
    ];

    protected $field = [
        'name' => '用户名',
        'uname' => '账号',
        'role_uuid' => '角色uuid',
        'phone' => '电话',
        'gender' => '性别',
        'admin_uuid'=>'admin_uuid',
        'reset_password_note'=>'备注',
        'set_status_note'=>'备注',
        'status'=>'状态',
        'password'=>'密码',
        'old_password'=>'原密码',
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['name', 'uname', 'role_uuid','phone','gender'],
        'edit' => ['name', 'uname', 'role_uuid','phone','gender'],
        'resetPassword' => ['admin_uuid','reset_password_note'],
        'setStatus' => ['admin_uuid','set_status_note','status'],
        'setPermission' => ['role_uuid'],
        'editPassword' => ['password','old_password'],
    ];
    protected function checkPhone($value, $rule, $data){
        // 中国手机号专项校验（可选）
        if (strpos($value, '+86') === 0 || preg_match('/:ml-citation{ref="1,3" data="citationList"}-9]\d{9}$/', $value)) {
            if (!preg_match('/:ml-citation{ref="1,3" data="citationList"}-9]\d{9}$/', ltrim($value, '+86'))) {
                return '中国大陆手机号需为11位有效数字';
            }
        }

        // 数据库唯一性校验（根据业务需求）
        if (isset($this->scene['edit']) && !empty($data['id'])) {
            $exists = db('admin')->where('phone', $value)->where('is_deleted',1)->where('uuid', '<>', $data['uuid'])->find();
        } else {
            $exists = db('admin')->where('phone', $value)->where('is_deleted',1)->find();
        }

        if ($exists) {
            return '手机号已存在';
        }
        return true;
    }
    protected function checkUname($value, $rule, $data){
        // 数据库唯一性校验（根据业务需求）
        if (isset($this->scene['edit']) && !empty($data['uuid'])) {
            $exists = db('admin')->where('uname', $value)->where('is_deleted',1)->where('uuid', '<>', $data['uuid'])->find();
        } else {
            $exists = db('admin')->where('uname', $value)->where('is_deleted',1)->find();
        }

        if ($exists) {
            return '账号已存在';
        }
        return true;
    }
}
