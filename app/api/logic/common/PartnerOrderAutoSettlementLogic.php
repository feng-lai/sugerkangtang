<?php

/**
 * Author Yacon
 * Date 2022/02/15 16:45
 */

namespace app\api\logic\common;

use app\api\model\Config;
use app\api\model\PartnerOrder;
use think\Db;

class PartnerOrderAutoSettlementLogic
{

    public static function sync()
    {
        try {
            Db::startTrans();
            $PartnerSettlement = Config::build()->where('key','PartnerSettlement')->value('value');
            PartnerOrder::build()
                ->field(['p.*'])
                ->alias('p')
                ->join('order o','o.order_id = p.order_id','left')
                ->where('o.pay_time','<=',date('Y-m-d H:i:s',time()-$PartnerSettlement*60*60*24))
                ->where('p.status',1)
                ->where('p.type',3)
                ->select()->each(function($item){
                    $item->save(['status'=>2]);
                    PartnerOrder::build()->settlement($item);
                });
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            return $e->getMessage();
        }

    }
}
