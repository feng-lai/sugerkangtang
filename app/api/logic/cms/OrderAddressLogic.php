<?php

namespace app\api\logic\cms;

use app\api\model\Admin;
use app\api\model\AdminLog;
use app\api\model\OrderAddress;
use think\Exception;
use think\Db;

/**
 * 订单地址逻辑
 */
class OrderAddressLogic
{
    static public function getMenu()
    {
        return '订单管理-订单列表';
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = OrderAddress::build()->where('uuid', $id)->where('is_deleted', 1)->findOrFail();
        AdminLog::build()->add($userInfo['uuid'], self::getMenu(), '查看订单地址');
        return $data;
    }


    static public function cmsEdit($request, $userInfo)
    {
        try {
            $user = OrderAddress::build()->where('uuid', $request['uuid'])->findOrFail();
            $user->save($request);
            AdminLog::build()->add($userInfo['uuid'], self::getMenu(), '修改订单地址');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }


}
