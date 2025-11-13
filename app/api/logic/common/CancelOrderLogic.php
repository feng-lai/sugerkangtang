<?php

/**
 * Author Yacon
 * Date 2022/02/15 16:45
 */

namespace app\api\logic\common;


use app\api\model\Config;
use app\api\model\Order;
use app\api\model\OrderLog;
use think\Db;

class CancelOrderLogic
{

    public static function sync()
    {
        try {
            Db::startTrans();
            $time = Config::build()->where('key', 'OrderCancelTime')->value('value');
            $data = Order::build()->field('uuid,order_id,create_time,site_id')->where('status', 1)->select();
            foreach ($data as $k => $v) {
                if (time() - strtotime($v['create_time']) > $time * 3600) {
                    Order::build()->where('uuid', $v['uuid'])->update([
                        'status' => 5,
                        'cancel_time' => now_time(time()),
                        'reason' => '支付超时自动取消',
                        'update_time' => now_time(time())
                    ]);
                    //订单日志
                    OrderLog::build()->save([
                        'uuid' => uuid(),
                        'order_id' => $v['order_id'],
                        'create_time' => now_time(time()),
                        'update_time' => now_time(time()),
                        'name' => '取消订单',
                        'content' => ['取消原因' => '支付超时自动取消'],
                        'site_id' => $v['site_id']
                    ]);
                }
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new \Exception($e->getMessage());
        }
    }
}
