<?php

namespace app\api\logic\cms;

use app\api\model\Admin;
use app\api\model\AdminLog;
use app\api\model\Help;
use think\Exception;
use think\Db;

/**
 * 帮助手册逻辑
 */
class HelpLogic
{
    static public function cmsList($request, $userInfo)
    {
        $where['is_deleted'] = 1;
        $request['name']?$where['name'] = ['like', '%'.trim($request['name']).'%']:'';
        $request['help_category_uuid']?$where['help_category_uuid'] = trim($request['help_category_uuid']):'';
        $result = Help::build()->where($where)
            ->order('create_time asc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])
            ->each(function ($item) {
                $item['admin_name'] = Admin::build()->where('uuid',$item['admin_uuid'])->value('name');
            });
        AdminLog::build()->add($userInfo['uuid'], '内容管理-帮助手册', '查看列表');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = Help::build()->where('uuid', $id)->where('is_deleted', 1)->findOrFail();
        AdminLog::build()->add($userInfo['uuid'], '内容管理-帮助手册', '查看详情');
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            $request['uuid'] = uuid();
            $request['create_time'] = now_time(time());
            $request['update_time'] = now_time(time());
            $request['admin_uuid'] = $userInfo['uuid'];
            Help::build()->insert($request);
            AdminLog::build()->add($userInfo['uuid'], '内容管理-帮助手册', '新增帮助手册-'.$request['name']);
            return $request['uuid'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo)
    {
        try {
            $user = Help::build()->where('uuid', $request['uuid'])->findOrFail();
            $user->save($request);
            AdminLog::build()->add($userInfo['uuid'], '内容管理-帮助手册', '编辑帮助手册-'.$user['name']);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id, $userInfo)
    {
        try {
            $data = Help::build()->where('uuid',$id)->findOrFail();
            $data->save(['is_deleted' => 2]);
            AdminLog::build()->add($userInfo['uuid'], '内容管理-帮助手册', '删除帮助手册-'.$data['name']);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function setStatus($request,$userInfo){
        $banner = Help::build()->where('uuid',$request['uuid'])->where('is_deleted',1)->findOrFail();
        $banner->save(['status'=>$request['status']]);
        AdminLog::build()->add($userInfo['uuid'], '内容管理-帮助手册', '帮助手册设置状态');
        return true;
    }

}
