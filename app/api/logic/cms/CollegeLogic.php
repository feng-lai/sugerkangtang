<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\College;
use think\Exception;
use think\Db;

/**
 * 书院逻辑
 */
class CollegeLogic
{
    static public function cmsList($request, $userInfo)
    {
        $result = College::build();
        if ($request['name']) $result = $result->where('name', 'like', '%' . $request['name'] . '%');
        $result = $result->where('is_deleted', 1)->order('create_time desc')->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
        AdminLog::build()->add($userInfo['uuid'], '书院', '查询列表', $request);
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = College::build()->where('uuid', $id)->where('is_deleted', 1)->findOrFail();
        AdminLog::build()->add($userInfo['uuid'], '书院', '查询详情:' . $data->name, $id);
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            if(College::build()->where('is_deleted',1)->where('name',$request['name'])->count()){
                return ['msg'=>'书院已存在'];
            }
            $data = [
                'uuid' => uuid(),
                'name' => $request['name'],
                'img' => $request['img'],
                'dsc' => $request['dsc'],
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
            ];
            College::build()->insert($data);
            AdminLog::build()->add($userInfo['uuid'], '书院', '新增：' . $data['name'], $request);
            return $data['uuid'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo, $uuid)
    {
        try {
            if(College::build()->where('is_deleted',1)->where('name',$request['name'])->where('uuid','<>',$uuid)->count()){
                return ['msg'=>'书院已存在'];
            }
            $user = College::build()->where('uuid', $uuid)->findOrFail();
            $user->save($request);
            AdminLog::build()->add($userInfo['uuid'], '书院', '更新：' . $request['name'], $request);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id, $userInfo)
    {
        try {
            College::build()->whereIn('uuid', explode(',', $id))->update(['is_deleted' => 2]);
            AdminLog::build()->add($userInfo['uuid'], '书院', '删除：' . $id, $id);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
