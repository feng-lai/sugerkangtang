<?php

namespace app\api\logic\cms;

use app\api\model\Admin;
use app\api\model\AdminLog;
use app\api\model\HelpCategory;
use think\Exception;
use think\Db;

/**
 * 帮助手册-分类逻辑
 */
class HelpCategoryLogic
{
    static public function cmsList($request, $userInfo)
    {
        $result = HelpCategory::build();
        $result = $result
            ->where('is_deleted', 1)
            ->order('create_time asc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
        AdminLog::build()->add($userInfo['uuid'], '内容管理-帮助手册分类', '查看列表');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = HelpCategory::build()->where('uuid', $id)->where('is_deleted', 1)->findOrFail();
        AdminLog::build()->add($userInfo['uuid'], '内容管理-帮助手册分类', '查看详情');
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            $request['uuid'] = uuid();
            $request['create_time'] = now_time(time());
            $request['update_time'] = now_time(time());
            HelpCategory::build()->insert($request);
            AdminLog::build()->add($userInfo['uuid'], '内容管理-帮助手册分类', '新增帮助手册分类-'.$request['name']);
            return $request['uuid'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo)
    {
        try {
            $user = HelpCategory::build()->where('uuid', $request['uuid'])->findOrFail();
            $user->save($request);
            AdminLog::build()->add($userInfo['uuid'], '内容管理-帮助手册分类', '编辑帮助手册分类-'.$user['name']);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id, $userInfo)
    {
        try {
            $data = HelpCategory::build()->where('uuid',$id)->findOrFail();
            $data->save(['is_deleted' => 2]);
            AdminLog::build()->add($userInfo['uuid'], '内容管理-帮助手册分类', '删除帮助手册分类-'.$data['name']);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

}
