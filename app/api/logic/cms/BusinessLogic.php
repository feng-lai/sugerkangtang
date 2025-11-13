<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\Business;
use think\Exception;
use think\Db;

/**
 * 标签逻辑
 */
class BusinessLogic
{
    static public function cmsList($request, $userInfo)
    {
        $result = Business::build()->where('is_deleted', 1)->order('create_time desc')->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
        AdminLog::build()->add($userInfo['uuid'], '用户管理', '企业管理', '','');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = Business::build()->where('uuid', $id)->where('is_deleted', 1)->findOrFail();
        AdminLog::build()->add($userInfo['uuid'], '用户管理', '企业管理', '','');
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            if(!$request['name']){
                return ['msg' => '名称不能为空'];
            }
            //标签名是否重复
            if (Business::build()->where('name', $request['name'])->where('is_deleted', 1)->count()) {
                return ['msg' => '名称已存在'];
            }
            $data = [
                'uuid' => uuid(),
                'name' => $request['name'],
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
            ];
            Business::build()->insert($data);
            AdminLog::build()->add($userInfo['uuid'], '用户管理', '企业管理', '',$data);
            return $data['uuid'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo, $uuid)
    {
        try {
            if(!$request['name']){
                return ['msg' => '名称不能为空'];
            }
            //标签名是否重复
            if (Business::build()->where('name', $request['name'])->where('is_deleted', 1)->where('uuid','<>',$uuid)->count()) {
                return ['msg' => '名称已存在'];
            }
            $old  = Business::build()->where('uuid', $uuid)->where('is_deleted', 1)->findOrFail();
            $user = Business::build()->where('uuid', $uuid)->findOrFail();
            $user->save($request);
            AdminLog::build()->add($userInfo['uuid'], '用户管理', '企业管理', $old,$user);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id, $userInfo)
    {
        try {
            $data = Business::build()->where('uuid', $id)->findOrFail();
            $data->save(['is_deleted' => 2]);
            AdminLog::build()->add($userInfo['uuid'], '用户管理', '企业管理', '',$data);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
