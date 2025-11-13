<?php

/**
 * Author Yacon
 * Date 2022/02/15 16:45
 */

namespace app\api\logic\common;

use app\api\model\Config;
use app\api\model\Order;
use app\api\model\OrderLog;
use app\api\model\Retail;
use app\api\model\User;
use think\Db;

class AutoConfirmOrderLogic
{

    public static function sync()
    {
        try {
            Db::startTrans();
            $time = Config::build()->where('key', 'OrderConfirm')->value('value');
            $data = Order::build()
                ->field('uuid,ship_time,order_id,site_id')
                ->where([
                    'is_deleted'=>1,
                    'status'=>3,
                ])
                ->select();
            foreach($data as $v){
                if(time() - strtotime($v['ship_time']) > $time*3600){
                    //完成订单
                    Order::build()->where('uuid', $v['uuid'])->update(['status'=>4,'confirm_time'=>now_time(time())]);
                    //订单记录
                    OrderLog::build()->save([
                        'uuid' => uuid(),
                        'order_id' => $v['order_id'],
                        'name'=>'确认收货',
                        'site_id'=>$v['site_id'],
                        'create_time' => now_time(time()),
                        'update_time' => now_time(time()),
                    ]);
                }
            }
            Db::commit();
            return true;
        }catch (\Exception $e){
            Db::rollback();
        }

    }
}
