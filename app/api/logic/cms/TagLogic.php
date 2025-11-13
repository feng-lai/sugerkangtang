<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\Tag;
use think\Exception;
use think\Db;

/**
 * 标签逻辑
 */
class TagLogic
{
    static public function cmsList($request, $userInfo)
    {
        $result = Tag::build()->field('uuid,sort,name,update_time');
        if ($request['name']) $result = $result->where('name', 'like', '%' . $request['name'] . '%');
        $result = $result->where('is_deleted', 1)->order('sort asc')->order('update_time desc')->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
        AdminLog::build()->add($userInfo['uuid'], '拼课标签', '查询列表', $request);
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = Tag::build()->where('uuid', $id)->where('is_deleted', 1)->findOrFail();
        AdminLog::build()->add($userInfo['uuid'], '拼课标签', '查询详情:' . $data->name, $id);
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            //标签名是否重复
            if (Tag::build()->where('name', $request['name'])->where('is_deleted', 1)->count()) {
                return ['msg' => '名称已存在'];
            }
            $data = [
                'uuid' => uuid(),
                'name' => $request['name'],
                'sort' => $request['sort'],
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
            ];
            Tag::build()->insert($data);
            AdminLog::build()->add($userInfo['uuid'], '拼课标签', '新增：' . $data['name'], $request);
            return $data['uuid'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo, $uuid)
    {
        try {
            //标签名是否重复
            if (Tag::build()->where('name', $request['name'])->where('is_deleted', 1)->where('uuid','<>',$uuid)->count()) {
                return ['msg' => '名称已存在'];
            }
            $user = Tag::build()->where('uuid', $uuid)->findOrFail();
            $user->save($request);
            AdminLog::build()->add($userInfo['uuid'], '拼课标签', '更新：' . $request['name'], $request);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id, $userInfo)
    {
        try {
            Tag::build()->whereIn('uuid', explode(',', $id))->update(['is_deleted' => 2]);
            AdminLog::build()->add($userInfo['uuid'], '拼课标签', '删除：' . $id, $id);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
