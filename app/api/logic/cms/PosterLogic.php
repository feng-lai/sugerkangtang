<?php

namespace app\api\logic\cms;

use app\api\model\Admin;
use app\api\model\AdminLog;
use app\api\model\Poster;
use think\Exception;
use think\Db;

/**
 * 首页弹窗海报管理逻辑
 */
class PosterLogic
{
    static public function cmsList($request, $userInfo)
    {
        $result = Poster::build();
        if ($request['status']) $result = $result->where('status', '=', $request['status']);
        if ($request['name']) $result = $result->where('name', 'like', '%'.$request['name'].'%');
        $result = $result
            ->where('is_deleted', 1)
            ->order('create_time asc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])
            ->each(function ($item) {
                $item->admin_name = Admin::build()->where('uuid', $item->admin_uuid)->value('name');
            });
        AdminLog::build()->add($userInfo['uuid'], '内容管理-首页弹窗海报管理', '查看列表');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = Poster::build()->where('uuid', $id)->where('is_deleted', 1)->findOrFail();
        AdminLog::build()->add($userInfo['uuid'], '内容管理-首页弹窗海报管理', '查看详情');
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            $data = [
                'uuid' => uuid(),
                'img' => $request['img'],
                'name' => $request['name'],
                'status' => $request['status'],
                'site_id' => $request['site_id'],
                'admin_uuid'=>$userInfo['uuid'],
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
            ];
            Poster::build()->insert($data);
            AdminLog::build()->add($userInfo['uuid'], '内容管理-首页弹窗海报管理', '新增海报'.$data['name']);
            return $data['uuid'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo)
    {
        try {
            $user = Poster::build()->where('uuid', $request['uuid'])->findOrFail();
            $user->save($request);
            AdminLog::build()->add($userInfo['uuid'], '内容管理-首页弹窗海报管理', '编辑海报'.$user->name);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id, $userInfo)
    {
        try {
            $data = Poster::build()->where('uuid',$id)->findOrFail();
            $data->save(['is_deleted' => 2]);
            AdminLog::build()->add($userInfo['uuid'], '内容管理-首页弹窗海报管理', '删除');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function setStatus($request,$userInfo){
        $banner = Poster::build()->where('uuid',$request['uuid'])->where('is_deleted',1)->findOrFail();
        $banner->save(['status'=>$request['status']]);
        AdminLog::build()->add($userInfo['uuid'], '内容管理-首页弹窗海报管理', '设置状态');
        return true;
    }
}
