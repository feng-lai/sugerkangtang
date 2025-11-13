<?php

namespace app\api\logic\cms;

use app\api\model\Admin;
use app\api\model\AdminLog;
use app\api\model\AdminToken;
use app\api\model\AdminRole;
use app\api\model\College;
use think\Exception;
use think\Db;
use app\common\tools\Sync;

/**
 * 后台用户-逻辑
 * User: Yacon
 * Date: 2022-08-11
 * Time: 21:24
 */
class AdminLogic
{
    static public function cmsList($request, $userInfo)
    {
        $map['is_deleted'] = ['=', 1];
        if ($request['keyword']) {
            $map['name|phone|uname'] = ['like', '%' . $request['keyword'] . '%'];
        }
        if ($request['role_uuid']) {
            $map['role_uuid'] = ['like', '%' . $request['role_uuid'] . '%'];
        }
        $result = Admin::build()
            ->field(
                'uuid,
                name,
                status,
                phone,
                gender,
                uname,
                status,
                last_login,
                role_uuid,
                outline_type'
            )
            ->where($map)
            ->order('create_time desc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
        foreach ($result as &$item) {
            $item->role_name = AdminRole::whereIn('uuid', $item['role_uuid'])->column('name');
            if($item->outline_type){
                $item->is_outline = 1;
            }else{
                $item->is_outline = 2;
            }
        }
        AdminLog::build()->add($userInfo['uuid'], '系统设置-管理员管理', '查看列表');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        AdminLog::build()->add($userInfo['uuid'], '系统设置-管理员管理', '查看详情');
        $admin = Admin::build()
            ->where('uuid', $id)
            ->where('is_deleted', '=', 1)
            ->field('
                uuid,
                name,
                status,
                phone,
                gender,
                uname,
                status,
                last_login,
                role_uuid
            '
            )
            ->find();
        $admin->role_name = AdminRole::whereIn('uuid', $admin['role_uuid'])->column('name');
        return $admin;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            Db::startTrans();
            AdminRole::build()->findOrFail($request['role_uuid']);
            $data = [
                'uuid' => uuid(),
                'name' => $request['name'],
                'password' => md6($request['password']),
                'uname' => $request['uname'],
                'gender' => $request['gender'],
                'phone' => $request['phone'],
                'role_uuid' => $request['role_uuid'],
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
            ];
            Admin::build()->save($data);
            //添加token
            $token = AdminToken::build();
            $token->uuid = uuid();
            $token->admin_uuid = $data['uuid'];
            $token->create_time = now_time(time());
            $token->update_time = now_time(time());
            $token->save();
            Db::commit();
            AdminLog::build()->add($userInfo['uuid'], '系统设置-管理员管理', '新增管理员’' . $data['name'] . '‘');
            return $data['uuid'];
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo)
    {
        try {
            $user = Admin::build()->where('uuid', $request['uuid'])->find();
            if ($request['password']) {
                $request['password'] = md6($request['password']);
            } else {
                unset($request['password']);
            }
            $user->save($request);
            AdminLog::build()->add($userInfo['uuid'], '系统设置-管理员管理', '编辑管理员’' . $user['name'] . '‘');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id, $userInfo)
    {
        try {
            Admin::build()->whereIn('uuid', explode(',', $id))->where('is_deleted', 1)->select()->each(function ($item) {
                if(!$item->outline_type){
                    Admin::build()->where('uuid', $item['uuid'])->update(['is_deleted' => 2]);
                }
            });
            AdminLog::build()->add($userInfo['uuid'], '系统设置-管理员管理', '删除管理员');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function setPermission($request, $userInfo)
    {
        try {
            $admin = Admin::build()->where('uuid', $request['uuid'])->findOrFail();
            $admin->save(['role_uuid' => $request['role_uuid']]);
            AdminLog::build()->add($userInfo['uuid'], '系统设置-管理员管理', '权限设置');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }


    static public function resetPassword($request, $userInfo)
    {
        try {
            Admin::build()->whereIn('uuid', $request['admin_uuid'])->where('is_deleted', 1)->update(['password' => md6('123456'), 'reset_password_note' => $request['reset_password_note']]);
            AdminLog::build()->add($userInfo['uuid'], '系统设置-管理员管理', '重置密码');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }


    static public function setStatus($request, $userInfo)
    {
        try {
            Admin::build()->whereIn('uuid', $request['admin_uuid'])->where('is_deleted', 1)->update(['status' => $request['status'], 'set_status_note' => $request['set_status_note']]);
            AdminLog::build()->add($userInfo['uuid'], '系统设置-管理员管理', '设置状态');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function editPassword($request, $userInfo)
    {
        try {
            $admin = Admin::build()->where('uuid', $request['uuid'])->findOrFail();
            if ($admin['password'] != md6($request['old_password'])) {
                return ['msg' => '原密码不正确'];
            }
            $admin->save(['password' => md6($request['password'])]);
            AdminLog::build()->add($userInfo['uuid'], '系统设置-管理员管理', '修改密码');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
