<?php

namespace app\api\logic\cms;

use app\api\model\Admin;
use app\api\model\AdminLog;
use app\api\model\OrderLog;
use app\api\model\OrderPath;
use app\api\model\Order;
use think\Exception;
use think\Db;

/**
 * 物流
 */
class OrderPathLogic
{
    static public function menu()
    {
        return '订单管理-订单列表';
    }

    static public function cmsList($request, $userInfo)
    {
        try {
            $order = Order::build()->where('order_id', $request['order_id'])->findOrFail();
            if(!$order->com || !$order->num){ 
                return [];
            }
            OrderPath::build()->getPath(['com' => $order->com, 'num' => $order->num, 'order_id' => $request['order_id']]);
            $result = OrderPath::build()->field('time,context,status')->where('order_id', $request['order_id'])->select();
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '查看物流');
            return $result;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo){
        try {
            $order = Order::build()->where('order_id', $request['order_id'])->findOrFail();
            $order->save($request);
            OrderPath::build()->where('order_id', $request['order_id'])->delete();
            OrderPath::build()->getPath($request);
            //订单日志
            OrderLog::build()->save([
                'uuid' => uuid(),
                'order_id' => $request['order_id'],
                'name'=>'修改发货',
                'admin_uuid'=>$userInfo['uuid'],
                'content'=>['物流公司'=>$request['com'],'快递单号'=>$request['num'],'备注信息'=>$request['order_path_note']],
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s'),
            ]);
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '更新物流');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

}
