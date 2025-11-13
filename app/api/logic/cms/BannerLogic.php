<?php

namespace app\api\logic\cms;

use app\api\model\Admin;
use app\api\model\AdminLog;
use app\api\model\Banner;
use think\Exception;
use think\Db;

/**
 * 轮播逻辑
 */
class BannerLogic
{
    static public function cmsList($request, $userInfo)
    {
        $result = Banner::build();
        if ($request['status']) $result = $result->where('status', '=', $request['status']);
        if ($request['name']) $result = $result->where('name', 'like', '%'.$request['name'].'%');
        $result = $result
            ->where('is_deleted', 1)
            ->order('order_number asc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function ($item) {
                $item->admin_name = Admin::build()->where('uuid',$item->admin_uuid)->value('name');
            });
        AdminLog::build()->add($userInfo['uuid'], '内容管理-轮播图管理', '查看列表');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = Banner::build()->where('uuid', $id)->where('is_deleted', 1)->findOrFail();
        AdminLog::build()->add($userInfo['uuid'], '内容管理-轮播图管理', '查看详情');
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            $request['uuid'] = uuid();
            $request['create_time'] = now_time(time());
            $request['update_time'] = now_time(time());
            $request['admin_uuid'] = $userInfo['uuid'];
            Banner::build()->insert($request);
            AdminLog::build()->add($userInfo['uuid'], '内容管理-轮播图管理', '新增轮播图-'.$request['name']);
            return $request['uuid'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo)
    {
        try {
            $user = Banner::build()->where('uuid', $request['uuid'])->findOrFail();
            $user->save($request);
            AdminLog::build()->add($userInfo['uuid'], '内容管理-轮播图管理', '编辑轮播图-'.$user['name']);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id, $userInfo)
    {
        try {
            $data = Banner::build()->where('uuid',$id)->findOrFail();
            $data->save(['is_deleted' => 2]);
            AdminLog::build()->add($userInfo['uuid'], '内容管理-轮播图管理', '删除轮播图-'.$data['name']);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function setStatus($request,$userInfo){
        $banner = Banner::build()->where('uuid',$request['uuid'])->where('is_deleted',1)->findOrFail();
        $banner->save(['status'=>$request['status']]);
        AdminLog::build()->add($userInfo['uuid'], '内容管理-轮播图管理', '轮播图设置状态');
        return true;
    }
}
