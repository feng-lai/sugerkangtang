<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\AdminMenu;
use think\Exception;
use think\Db;

/**
 * 后台菜单-逻辑
 * User: Yacon
 * Date: 2022-08-11
 * Time: 21:24
 */
class AdminMenuLogic
{
    static public function cmsList($request, $userInfo)
    {
        $map['is_deleted'] = ['=', 1];
        if ($request['level']) $map['level'] = ['=', $request['level']];
        if ($request['pid']) $map['pid'] = ['=', $request['pid']];
        if ($request['name']) $map['name'] = ['like','%'. $request['name'].'%'];
        if ($request['type']) $map['type'] = ['=', $request['type']];
        if ($request['status']) $map['status'] = ['=', $request['status']];
        if ($request['vis']) $map['vis'] = ['=', $request['vis']];
        $result = AdminMenu::build()->where($map)->order('create_time desc')->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
        foreach ($result as $v) {
            if ($v->pid) {
                $v->p_name = AdminMenu::build()->where('uuid', '=', $v->pid)->value('name');
            } else {
                $v->p_name = '';
            }
            $v->child = AdminMenu::build()->where('pid', $v['uuid'])->where('is_deleted', '=', 1)->order('level asc,create_time desc')->select();
            foreach ($v->child as $k2 => $v2) {
                if ($v2->pid) {
                    $v2->p_name = AdminMenu::build()->where('uuid', '=', $v2->pid)->value('name');
                } else {
                    $v2->p_name = '';
                }
                $v2->child = AdminMenu::build()->where('pid', $v2['uuid'])->where('is_deleted', '=', 1)->order('level asc,create_time desc')->select();
            }
        }
        AdminLog::build()->add($userInfo['uuid'], '权限管理', '菜单管理');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {

        $data = AdminMenu::build()
            ->where('uuid', $id)
            ->where('is_deleted', '=', 1)
            ->field('*')
            ->findOrFail();
        AdminLog::build()->add($userInfo['uuid'], '权限管理', '菜单管理');
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            $data = [
                'uuid' => uuid(),
                'name' => $request['name'],
                'url' => $request['url'],
                'pid' => $request['pid'],
                'vis'=>$request['vis'],
                'status'=>$request['status'],
                'type'=>$request['type'],
                'level' => $request['level'],
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
            ];
            AdminMenu::build()->save($data);
            AdminLog::build()->add($userInfo['uuid'], '系统设置-菜单管理', '新增菜单-'.$data['name']);
            return $data['uuid'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo)
    {
        try {
            $data = AdminMenu::build()->where('uuid', $request['uuid'])->where('is_deleted', '=', 1)->find();
            $data->save($request);
            AdminLog::build()->add($userInfo['uuid'], '系统设置-菜单管理', '编辑菜单-'.$data['name']);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id, $userInfo)
    {
        try {
            $data = AdminMenu::build()->whereIn('uuid',explode(',',$id))->where('is_deleted',1)->update(['is_deleted' => 2]);
            AdminLog::build()->add($userInfo['uuid'], '系统设置-菜单管理', '删除菜单');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
