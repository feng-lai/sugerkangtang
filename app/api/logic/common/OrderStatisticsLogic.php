<?php

/**
 * Author Yacon
 * Date 2022/02/15 16:45
 */

namespace app\api\logic\common;

use app\api\model\ActivitiesTurntable;
use app\api\model\DistributionStatistics;
use app\api\model\Order;
use app\api\model\User;

class OrderStatisticsLogic
{

    public static function sync()
    {

        // 获取大转盘获取奖励的分享次数
        $shareNum = ActivitiesTurntable::build()->where(['uuid' => 'f089ac9a14d3408a9b425af584b5eaa7'])->value('share_num');
        User::build()->where('is_deleted', 1)->update(['luck_share_num' => $shareNum]);


        $date = date('Y-m-d', strtotime('-1 day', time()));

        $where = "date_format('create_time','%Y-%m-%d') = {$date}";
        // 前一天订单的订单总收入
        $price = Order::build()->where($where)->where(['state' => ['in', [2, 3, 4]]])->sum('pay_price');
        // 前一天订单的订单数
        $number = Order::build()->where($where)->where(['state' => ['in', [2, 3, 4]]])->count();

        DistributionStatistics::build()->insert([
            'uuid' => uuid(),
            'create_time' => now_time(time()),
            'update_time' => now_time(time()),
            'date' => $date,
            'price' => $price,
            'number' => $number,
        ]);
    }
}
