<?php

/**
 * Author Yacon
 * Date 2022/02/15 16:45
 */

namespace app\api\logic\common;

use app\api\model\Goods;
use app\api\model\IntegralOrder;
use app\api\model\Message;
use app\api\model\Order;
use app\api\model\OrderGoods;
use app\api\model\OrderSetting;
use app\api\model\User;
use app\api\model\UserCommission;
use app\api\model\UserRelation;

class OrderReceiveStateLogic
{

    public static function sync()
    {

        // 将超时待付款的分销订单的状态改为已关闭
        Order::build()->where(['receive_auto_time' => ['<', now_time(time())], 'state' => 3])->update(['state' => 4]);

        // 将超时待收货的积分订单的状态改为已完成
        IntegralOrder::build()->where(['receive_auto_time' => ['<', now_time(time())], 'state' => 2])->update(['state' => 3]);

        // 自动收货后的操作
        $orders = Order::build()->where(['receive_auto_time' => ['<', now_time(time())], 'state' => 3])->select();
        foreach ($orders as $order) {

            // 发送消息
            Message::build()->add($order['user_uuid'], '订单状态变更通知', "您的订单已显示签收，请尽快确认收货", 7, $order['uuid']);
            Message::build()->add($order['user_uuid'], '佣金金额变动通知', "佣金已发放至您的余额，请注意查看", 10);

            // 更新商品的已售数量
            $orderGoodsList = OrderGoods::build()->where(['order_uuid' => $order['uuid']])->select();
            $orderGoodsList = objToArray($orderGoodsList);
            foreach ($orderGoodsList as $orderGoods) {
                $goods = Goods::build()->where(['uuid' => $orderGoods['goods_uuid']])->find();
                $goods['update_time'] = now_time(time());
                $goods['stock_frozen'] = $goods['stock_frozen'] - $orderGoods['number'] - $orderGoods['refund_num'];
                $goods['sale_num'] = $goods['sale_num'] + $orderGoods['number'] - $orderGoods['refund_num'];
                $goods['stock'] = $goods['stock'] + $orderGoods['refund_num'];
                $goods->save();
            }

            // 更新用户下单数量
            User::build()->where(['uuid' => $order['user_uuid']])->setInc('order_num', 1);

            // 发放佣金
            $users = UserRelation::build()->where(['new_user_uuid' => $order['user_uuid']])->select();
            foreach ($users as $uv) {
                $user = User::build()->where(['uuid' => $uv['user_uuid']])->find();

                // 发送消息
                Message::build()->add($user['uuid'], '佣金金额变动通知', '佣金已发放至您的余额，请注意查看', 10);

                $data = [
                    'uuid' => uuid(),
                    'create_time' => now_time(time()),
                    'update_time' => now_time(time()),
                    'order_uuid' => $order['uuid'],
                    'user_uuid' => $uv['user_uuid'],
                    'buy_user_uuid' => $order['user_uuid'],
                    'level' => $uv['level'],
                ];
                // 团购专员
                if ($user['access'] == 1) {
                    $user['commission'] = $order['commissioner'];
                    $data['commission'] = $order['commissioner'];
                }
                // 团购经纪人
                else if ($user['access'] == 2) {
                    $user['commission'] = $order['agent'];
                    $data['commission'] = $order['agent'];
                }

                $user->save();
                UserCommission::build()->insert($data);
            }
        }
    }
}
