<?php

namespace app\api\logic\cms;

use app\api\model\Admin;
use app\api\model\AdminLog;
use app\api\model\DictionaryType;
use think\Exception;
use think\Db;

/**
 * 字典类型逻辑
 */
class DictionaryTypeLogic
{
    static public function getMenu()
    {
        return '系统设置-字典类型管理';
    }

    static public function cmsList($request, $userInfo)
    {
        $result = DictionaryType::build();
        if ($request['status']) $result = $result->where('status', '=', $request['status']);
        if ($request['site_id']) $result = $result->where('site_id', '=', $request['site_id']);
        if ($request['keyword']) $result = $result->where('name|type', 'like', '%' . $request['keyword'] . '%');
        $result = $result
            ->where('is_deleted', 1)
            ->order('create_time desc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
        AdminLog::build()->add($userInfo['uuid'], self::getMenu(), '查看列表');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = DictionaryType::build()->where('uuid', $id)->where('is_deleted', 1)->findOrFail();
        AdminLog::build()->add($userInfo['uuid'], self::getMenu(), '查看详情');
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            $request['uuid'] = uuid();
            $request['create_time'] = now_time(time());
            $request['update_time'] = now_time(time());
            DictionaryType::build()->insert($request);
            AdminLog::build()->add($userInfo['uuid'], self::getMenu(), '新增-' . $request['name']);
            return $request['uuid'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo)
    {
        try {
            $user = DictionaryType::build()->where('uuid', $request['uuid'])->findOrFail();
            $user->save($request);
            AdminLog::build()->add($userInfo['uuid'], self::getMenu(), '编辑-' . $user['name']);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id, $userInfo)
    {
        try {
            $data = DictionaryType::build()->whereIn('uuid', $id)->update(['is_deleted' => 2,'update_time' => now_time(time())]);
            AdminLog::build()->add($userInfo['uuid'], self::getMenu(), '删除');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function setStatus($request, $userInfo)
    {
        $banner = DictionaryType::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
        $banner->save(['status' => $request['status']]);
        AdminLog::build()->add($userInfo['uuid'], self::getMenu(), '设置状态');
        return true;
    }
}
