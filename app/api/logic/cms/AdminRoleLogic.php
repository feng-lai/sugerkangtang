<?php

namespace app\api\logic\cms;

use app\api\model\Admin;
use app\api\model\AdminLog;
use app\api\model\AdminRole;
use app\api\model\AdminMenu;
use think\Exception;
use think\Db;

/**
 * 后台菜单-逻辑
 * User: Yacon
 * Date: 2022-08-11
 * Time: 21:24
 */
class AdminRoleLogic
{
    static public function cmsList($request,$userInfo)
    {
        $where = ['is_deleted' => 1];
        $request['keyword']?$where['name'] = ['like','%'.$request['keyword'].'%']:'';
        $list = AdminRole::build()->where($where)->order('create_time desc')->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function ($item) {
            $item->num = Admin::build()->whereRaw("JSON_CONTAINS(role_uuid, '\"".$item->uuid."\"')")->count();
            $item->admin = Admin::build()->field('uuid,name')->whereRaw("JSON_CONTAINS(role_uuid, '\"".$item->uuid."\"')")->select();
        });
        AdminLog::build()->add($userInfo['uuid'], '系统设置-角色管理', '角色管理');
        return $list;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = AdminRole::build()
            ->where('uuid', $id)
            ->where('is_deleted', '=', 1)
            ->field('*')
            ->find();
        $data->admin_uuid = Admin::build()->whereRaw("JSON_CONTAINS(role_uuid, '\"".$data->uuid."\"')")->column('uuid');
        AdminLog::build()->add($userInfo['uuid'], '系统设置-角色管理', '角色管理');
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            if(AdminRole::build()->where(['name' => $request['name']])->where('is_deleted',1)->count()){
                return ['msg'=>'角色名称已存在'];
            }
            $data = [
                'uuid' => uuid(),
                'name' => $request['name'],
                'desc' => $request['desc'],
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
            ];
            AdminRole::build()->save($data);
            AdminLog::build()->add($userInfo['uuid'], '系统设置-角色管理', '新增角色-'.$request['name']);
            return $data['uuid'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo)
    {
        try {
            if(in_array($request['name'],['超级管理员','联合出品方','特邀经销商','大区推广','渠道商'])){
                return ['msg'=>'内置的角色不可编辑'];
            }
            if(AdminRole::build()->where(['name' => $request['name']])->where('uuid','<>',$request['uuid'])->where('is_deleted',1)->count()){
                return ['msg'=>'角色名称已存在'];
            }
            $data = AdminRole::build()->where('uuid', $request['uuid'])->where('is_deleted',1)->findOrFail();
            $data->save($request);
            AdminLog::build()->add($userInfo['uuid'], '系统设置-角色管理', '编辑角色-'.$data['name']);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id, $userInfo)
    {
        try {
            $data = AdminRole::build()->whereIn('uuid', explode(',',$id))->where('is_deleted', 1)->update(['is_deleted' => 2]);
            AdminLog::build()->add($userInfo['uuid'], '系统设置-角色管理', '删除角色');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static function setStatus($request, $userInfo){
        if(!is_numeric($request['status'])){
            return ['msg'=>'状态不能为空'];
        }
        try {
            $data = AdminRole::build()->where('uuid',$request['uuid'])->where('is_deleted', 1)->findOrFail();
            $data->save(['status' => $request['status']]);
            AdminLog::build()->add($userInfo['uuid'], '系统设置-角色管理', '设置状态');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static function setAdmin($request, $userInfo){
        if(!$request['admin_uuid']){
            return ['msg'=>'管理员uuid不能为空'];
        }
        try {
            Db::startTrans();
            $data = AdminRole::build()->where('uuid',$request['uuid'])->where('is_deleted', 1)->findOrFail();
            if($data->status == 2){
                return ['msg'=>'未启用的角色不能添加人员'];
            }
            foreach ($request['admin_uuid'] as $k=>$v){
                $admin = Admin::where('uuid',$v)->findOrFail();
                if($admin->role_uuid){
                    $role_uuid = array_unique(array_merge($admin->role_uuid,[$request['uuid']]));
                }else{
                    $role_uuid[] = [$request['uuid']];
                }
                $admin->save(['role_uuid' => $role_uuid]);
            }
            Db::commit();
            AdminLog::build()->add($userInfo['uuid'], '系统设置-角色管理', '添加人员');
            return true;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

    static function setMenus($request, $userInfo)
    {
        if(!$request['menus']){
            return ['msg'=>'菜单不能为空'];
        }
        try {
            Db::startTrans();
            $data = AdminRole::build()->where('uuid',$request['uuid'])->where('is_deleted', 1)->findOrFail();
            $data->save(['menus' => $request['menus']]);
            Db::commit();
            AdminLog::build()->add($userInfo['uuid'], '系统设置-角色管理', '设置权限');
            return true;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }
}
