<?php

namespace app\api\logic\cms;

use app\api\model\Address;
use app\api\model\AdminLog;
use think\Exception;
use think\Db;

/**
 * 收货地址-逻辑
 */
class AddressLogic
{
    static public function menu()
    {
        return '用户管理-用户列表';
    }
    static public function Add($request, $userInfo)
    {
        try {
            if($request['is_default'] == 1) {
                Address::build()->where(['user_uuid'=>$request['user_uuid']])->update(['is_default'=>2]);
            }
            $request['uuid'] = uuid();
            $request['create_time'] = now_time(time());
            $request['update_time'] = now_time(time());
            Address::build()->save($request);
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '新增收货地址');
            return $request['uuid'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Edit($request, $userInfo)
    {
        try {
            $data = Address::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
            if($request['is_default'] == 1) {
                Address::build()->where(['user_uuid'=>$data['user_uuid']])->update(['is_default'=>2]);
            }
            $request['update_time'] = now_time(time());
            $data->save($request);
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '收货地址编辑');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }


    static public function List($request, $userInfo)
    {
        try {
            $where = [
                'is_deleted' => 1,
                'site_id' => $request['site_id'],
            ];
            $request['user_uuid']?$where['user_uuid'] = $request['user_uuid']:'';
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '查询收货地址列表');
            return Address::build()->where($where)->order('create_time desc')->paginate(['list_rows'=>$request['page_size'],'page'=>$request['page_index']])->toArray();
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Detail($uuid, $userInfo)
    {
        try {
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '查询收货地址详情');
            return Address::build()->where('is_deleted', 1)->where('uuid', $uuid)->findOrFail();
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Delete($uuid, $userInfo)
    {
        try {
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '删除收货地址');
            Address::build()->whereIn('uuid', explode(',',$uuid))->update(['is_deleted' => 2]);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function setDefault($uuid, $userInfo)
    {
        try {
            $data = Address::build()->where('uuid', $uuid)->findOrFail();
            Address::build()->where('user_uuid', $data->user_uuid)->update(['is_default'=>2]);
            $data->save(['is_default'=>1]);
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '设置默认收货地址');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

}
