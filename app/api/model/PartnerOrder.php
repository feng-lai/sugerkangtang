<?php

namespace app\api\model;

/**
 * 合伙人分销订单-模型
 */
class PartnerOrder extends BaseModel
{
    public static function build()
    {
        return new self();
    }

    //结算
    public function settlement($commission_order)
    {
        //钱包
        $retail = Retail::build()->where('user_uuid', $commission_order['user_uuid'])->find();
        if($retail){
            $retail->setInc('wallet', $commission_order['commission']);
            if($commission_order['type'] == 1){
                $type = 7;
            }
            if($commission_order['type'] == 2){
                $type = 8;
            }
            if($commission_order['type'] == 3){
                $type = 9;
            }
            //账单
            Bill::build()->insert([
                'uuid' => uuid(),
                'partner_order_id' => $commission_order['partner_order_id'],
                'price' => $commission_order['commission'],
                'site_id' => $commission_order['site_id'],
                'type' => $type,
                'bill_id' => 'BX' . getOrderNumber(),
                'user_uuid' => $commission_order['user_uuid'],
                'wallet' => $retail->wallet,
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
            ]);
        }

    }

    //线下分销结算
    public function settlement_outline($order,$retail,$status,$product_attribute)
    {
        //线下分销
        PartnerOrderOutline::build()->insert([
            'uuid' => uuid(),
            'order_id' => $order->order_id,
            'user_uuid' => $retail->user_uuid,
            'partner_uuid' => $retail->uuid,
            'partner_order_outline_id' => 'POM' . getOrderNumber(),
            'product_attribute_uuid' => $order->product_attribute_uuid,
            'product_uuid' => $order->product_uuid,
            'qty' => $order['qty'],
            'status' => $status,
            'price' => $order['price'],
            'total_price' => $order['price'] * $order['qty'],
            'commission' => $product_attribute->partner_producer * $order['qty'],
            'producer_uuid' => $retail->producer_uuid,
            'site_id' => $order['site_id'],
            'create_time' => now_time(time()),
            'update_time' => now_time(time())
        ]);
    }

}
