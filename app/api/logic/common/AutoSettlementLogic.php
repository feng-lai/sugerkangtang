<?php

/**
分销订单自动结算
 */

namespace app\api\logic\common;

use app\api\model\CommissionOrder;
use app\api\model\CommissionOrderOutline;
use think\Db;
use think\Exception;

class AutoSettlementLogic
{

    public static function sync()
    {
        try {
            Db::startTrans();
            CommissionOrder::build()
                ->field([
                    'c.*',
                    //'or.confirm_time',
                    //'o.after_sale_day',
                    //'o.is_after_sale'
                ])
                ->alias('c')
                ->join('order_detail o','o.order_id = c.order_id and o.product_attribute_uuid = c.product_attribute_uuid','left')
                ->join('order or','or.order_id = c.order_id','left')
                ->where(['c.status'=>1,'o.is_after_sale'=>1,'or.status'=>4])
                ->whereRaw("DATE_ADD(or.confirm_time, INTERVAL o.after_sale_day DAY) < NOW()")
                ->select()->each(function($item){
                    CommissionOrder::build()->where('uuid',$item['uuid'])->update(['status'=>2]);
                    CommissionOrderOutline::build()->where('order_id',$item['order_id'])->where('product_attribute_uuid',$item['product_attribute_uuid'])->update(['status'=>2]);
                    CommissionOrder::build()->settlement($item);
                });
            Db::commit();
            return true;
        }catch (\Exception $e){
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }
}
