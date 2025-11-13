<?php

namespace app\api\logic\mini;

use app\api\model\InvoiceTitle;
use think\Exception;
use think\Db;

/**
 * 发票抬头-逻辑
 */
class InvoiceTitleLogic
{
    static public function Add($request, $userInfo)
    {
        try {
            $request['uuid'] = uuid();
            $request['user_uuid'] = $userInfo['uuid'];
            $request['create_time'] = now_time(time());
            $request['update_time'] = now_time(time());
            InvoiceTitle::build()->save($request);
            return $request['uuid'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Edit($request, $userInfo)
    {
        try {
            $data = InvoiceTitle::build()->where('uuid', $request['uuid'])->where('user_uuid', $userInfo['uuid'])->where('is_deleted', 1)->findOrFail();
            $request['update_time'] = now_time(time());
            $data->save($request);
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
                'user_uuid' => $userInfo['uuid'],
            ];
            $request['type'] ? $where['type'] = $request['type'] : '';
            return InvoiceTitle::build()->where($where)->order('create_time desc')->select();
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Detail($uuid, $userInfo)
    {
        try {
            return InvoiceTitle::build()->where('is_deleted', 1)->where('user_uuid', $userInfo['uuid'])->where('uuid', $uuid)->findOrFail();
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Delete($uuid, $userInfo)
    {
        try {
            $data = InvoiceTitle::build()->where('uuid', $uuid)->where('user_uuid', $userInfo['uuid'])->where('is_deleted', 1)->findOrFail();
            $data->save(['is_deleted' => 2]);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

}
