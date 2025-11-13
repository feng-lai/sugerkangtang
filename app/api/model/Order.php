<?php

namespace app\api\model;

use app\api\controller\SmsReport;
use app\common\tools\wechatMsg;
use app\common\tools\WechatRefund;

/**
 * 订单-模型
 * User:
 * Date:
 * Time:
 */
class Order extends BaseModel
{
    public static function build()
    {
        return new self();
    }

    //发货
    public function ship($order)
    {
        $user = User::build()->where('uuid', $order->user_uuid)->find();
        if ($user->ship_order_msg == 2) {
            return true;
        }
        /**发送订阅消息**/
        $wechatMsg = new wechatMsg();
        $res = $wechatMsg->handle('SOpb59NGJojs29wbrBvimt49k1YeVEmt2tdWDKGhd-0', 'pagesA/orderDetails?id=' . $order->order_id, $user->openid, [
            'time2' => ['value' => now_time(time())],
            'character_string3' => ['value' => $order->num],
            'character_string7' => ['value' => $order->order_id],
        ]);

        if(!$order->medical_report_uuid){
            //发送短信
            $phone = User::build()->where('uuid', $order->user_uuid)->value('phone');
            $send = new SmsReport();
            $res = $send->send_notice($phone);
        }

        //发送通知
        $address = OrderAddress::build()->where('uuid', $order->order_address_uuid)->find();
        Msg::build()->insert([
            'uuid' => uuid(),
            'order_id' => $order->order_id,
            'type' => 1,
            'user_uuid' => $order->user_uuid,
            'title' => '您购买的订单已发货',
            'content' => '物流公司:' . $order->com_name . '，发货时间：' . now_time(time()) . ',收货地址：' . $address->province . $address->city . $address->district . $address->address,
            'site_id' => $order->site_id,
            'create_time' => now_time(time()),
            'update_time' => now_time(time()),
        ]);


    }

    //待付款
    public function pendding_msg($order_id)
    {
        $time = Config::build()->where('key', 'OrderCancelTime')->value('value') * 60;
        $order = Order::where('order_id', $order_id)->find();
        $order_detail = OrderDetail::build()->alias('o')->join('product p', 'p.uuid = o.product_uuid')->where('o.order_id', $order_id)->column('p.name');
        $pro = implode(',', $order_detail);
        $pendding_order_msg = User::build()->where('uuid', $order->user_uuid)->value('pendding_order_msg');
        if ($pendding_order_msg == 1) {
            if (Msg::build()->where(['user_uuid' => $order->user_uuid, 'order_id' => $order_id])->count()) {
                return true;
            }
            //发送通知
            Msg::build()->insert([
                'uuid' => uuid(),
                'order_id' => $order_id,
                'type' => 1,
                'user_uuid' => $order->user_uuid,
                'title' => '待付款提醒',
                'content' => '您有一笔订单（编号:' . $order_id . '）待支付，商品：' . $pro . ',金额：' . $order->price . '元，' . $time . '分钟后自动取消，点击立即支付',
                'site_id' => $order->site_id,
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
            ]);
        }
    }

    public function pay_success($data)
    {
        if (Bill::build()->where(['site_id' => $data['site_id'], 'order_id' => $data['order_id'], 'user_uuid' => $data['user_uuid'], 'type' => 1])->find()) {
            return true;
        }
        Bill::build()->insert([
            'uuid' => uuid(),
            'order_id' => $data['order_id'],
            'user_uuid' => $data['user_uuid'],
            'bill_id' => 'BX' . getOrderNumber(),
            'create_time' => now_time(time()),
            'update_time' => now_time(time()),
            'type' => 1,
            'price' => $data['price'],
            'site_id' => $data['site_id'],
        ]);
        //配置是否开启分销
        $is = Config::build()->where('key','IsRetail')->value('value');
        if($is == 1){
            OrderDetail::build()->setCommissionOrder($data['order_id']);
        }
        //配置是否开启合伙人分销
        $IsPartner = Config::build()->where('key','IsPartner')->value('value');
        if($IsPartner == 1){
            OrderDetail::build()->setPartnerOrder($data['order_id']);
        }
    }

    public function cancel($data)
    {
        //恢复商品库存
        OrderDetail::build()->where('order_id', $data['order_id'])->select()->each(function ($item) {
            ProductAttribute::build()->where('uuid', $item['product_attribute_uuid'])->setInc('qty', $item['qty']);
            Product::build()->where('uuid', $item['product_uuid'])->setInc('qty', $item['qty']);
            //佣金取消结算
            CommissionOrder::build()->where('order_id', $item['order_id'])->where('product_attribute_uuid', $item['product_attribute_uuid'])->update(['status' => 3]);
        });

    }

    public function refund($data, $fee, $content)
    {
        $refund = new WechatRefund();
        $res = $refund->refund($data->trade_no, $data->order_id, $data->price, $fee, $content);
        if ($res) {
            //账单
            Bill::build()->insert([
                'uuid' => uuid(),
                'order_id' => $data['order_id'],
                'user_uuid' => $data['user_uuid'],
                'bill_id' => 'BX' . getOrderNumber(),
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
                'type' => 4,
                'price' => $fee,
                'site_id' => $data['site_id'],
            ]);
        }

        return $res;
    }

}
