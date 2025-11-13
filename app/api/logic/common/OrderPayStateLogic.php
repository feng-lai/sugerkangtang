<?php

/**
 * Author Yacon
 * Date 2022/02/15 16:45
 */

namespace app\api\logic\common;

use app\api\model\IntegralGoods;
use app\api\model\IntegralOrder;
use app\api\model\Order;
use app\api\model\OrderGoods;
use app\api\model\Questionnaire;

class OrderPayStateLogic
{

    public static function sync()
    {

        // 到达开始时间，问卷调查的状态改为进行中
        Questionnaire::build()->where(['is_deleted' => 1, 'begin_time' => ['<=', now_time(time())], 'end_time' => ['>', now_time(time())]])->update(['state' => 2]);
        // 到达结束时间，问卷调查的状态改为已结束
        Questionnaire::build()->where(['is_deleted' => 1, 'end_time' => ['<=', now_time(time())]])->update(['state' => 3]);
        // 到达结束时间，问卷调查的状态改为已结束
        Questionnaire::build()->where(['is_deleted' => 1, 'hide_time' => ['<=', now_time(time())]])->update(['state' => 4]);

        // 更改积分商品为兑换中
        IntegralGoods::build()->where(['time_type' => 2, 'begin_time' => ['<=', now_time(time())], 'end_time' => ['>', now_time(time())]])->update(['state' => 1]);
        // 更改积分商品为已下架
        IntegralGoods::build()->where(['time_type' => 2, 'end_time' => ['<=', now_time(time())]])->update(['state' => 2]);

        // 将超时待付款的分销订单的状态改为已关闭
        Order::build()->where(['pay_expire_time' => ['<=', now_time(time())], 'state' => 1])->update(['state' => 5]);

        // 将超时待付款的积分订单的状态改为已关闭
        IntegralOrder::build()->where(['refund_price_check_time' => ['<=', now_time(time())], 'freight_pay' => 1, 'state' => 1])->update(['state' => 4]);

        // 将退货售后超时的分销订单改为正常
        $orderGoods =  OrderGoods::build()->where(['refund_expire_time' => ['<=', now_time(time())], 'state' => 3, 'send_state' => 1, 'refund_check' => 2])->select();
        $orderGoods = objToArray($orderGoods);
        foreach ($orderGoods as $k => $v) {
            $orderGoods['state'] = 1;
            $orderGoods['send_state'] = 0;
            $orderGoods['refund_price'] = 0;
            $orderGoods['refund_num'] = 0;
            $orderGoods['refund_check'] = 0;
            $orderGoods['refund_check_result'] = 0;
            $orderGoods['refund_time'] = null;
            $orderGoods['refund_no'] = null;
            $orderGoods->save();

            // 设置订单状态
            Order::build()->setOrderState($v['order_uuid']);
        }
    }
}
