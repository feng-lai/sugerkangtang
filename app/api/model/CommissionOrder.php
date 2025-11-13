<?php

namespace app\api\model;

/**
 * 分销订单-模型
 */
class CommissionOrder extends BaseModel
{
    public static function build()
    {
        return new self();
    }

    //结算
    public function settlement($commission_order)
    {
        //钱包
        $retail = Retail::build()->where('uuid', $commission_order['retail_uuid'])->findOrFail();
        $retail->setInc('wallet', $commission_order['commission']);
        //账单
        Bill::build()->insert([
            'uuid' => uuid(),
            'commission_order_id' => $commission_order['commission_order_id'],
            'price' => $commission_order['commission'],
            'site_id' => $commission_order['site_id'],
            'type' => 2,
            'bill_id' => 'BX' . getOrderNumber(),
            'user_uuid' => $commission_order['user_uuid'],
            'wallet' => $retail->wallet,
            'create_time' => now_time(time()),
            'update_time' => now_time(time()),
        ]);
    }

    //线下分销结算
    public function settlement_outline($order,$retail,$status,$product_attribute)
    {
        //线下分销
        CommissionOrderOutline::build()->insert([
            'uuid' => uuid(),
            'order_id' => $order->order_id,
            'user_uuid' => $retail->user_uuid,
            'retail_uuid' => $retail->uuid,
            'commission_order_outline_id' => 'COM' . getOrderNumber(),
            'product_attribute_uuid' => $order->product_attribute_uuid,
            'product_uuid' => $order->product_uuid,
            'qty' => $order['qty'],
            'status' => $status,
            'price' => $order['price'],
            'total_price' => $order['price'] * $order['qty'],
            'commission' => $product_attribute->profit_one * $order['qty'],
            'producer_uuid' => $retail->producer_uuid,
            'outline_type'=>1,
            'site_id' => $order['site_id'],
            'create_time' => now_time(time()),
            'update_time' => now_time(time())
        ]);
        CommissionOrderOutline::build()->insert([
            'uuid' => uuid(),
            'order_id' => $order->order_id,
            'user_uuid' => $retail->user_uuid,
            'retail_uuid' => $retail->uuid,
            'commission_order_outline_id' => 'COM' . getOrderNumber(),
            'product_attribute_uuid' => $order->product_attribute_uuid,
            'product_uuid' => $order->product_uuid,
            'qty' => $order['qty'],
            'status' => $status,
            'price' => $order['price'],
            'total_price' => $order['price'] * $order['qty'],
            'commission' => $product_attribute->profit_two * $order['qty'],
            'producer_uuid' => $retail->producer_uuid,
            'dealer_uuid' => $retail->dealer_uuid,
            'outline_type'=>2,
            'site_id' => $order['site_id'],
            'create_time' => now_time(time()),
            'update_time' => now_time(time())
        ]);
        CommissionOrderOutline::build()->insert([
            'uuid' => uuid(),
            'order_id' => $order->order_id,
            'user_uuid' => $retail->user_uuid,
            'retail_uuid' => $retail->uuid,
            'commission_order_outline_id' => 'COM' . getOrderNumber(),
            'product_attribute_uuid' => $order->product_attribute_uuid,
            'product_uuid' => $order->product_uuid,
            'qty' => $order['qty'],
            'status' => $status,
            'price' => $order['price'],
            'total_price' => $order['price'] * $order['qty'],
            'commission' => $product_attribute->profit_three * $order['qty'],
            'producer_uuid' => $retail->producer_uuid,
            'dealer_uuid' => $retail->dealer_uuid,
            'region_uuid' => $retail->region_uuid,
            'outline_type'=>3,
            'site_id' => $order['site_id'],
            'create_time' => now_time(time()),
            'update_time' => now_time(time())
        ]);
        CommissionOrderOutline::build()->insert([
            'uuid' => uuid(),
            'order_id' => $order->order_id,
            'user_uuid' => $retail->user_uuid,
            'retail_uuid' => $retail->uuid,
            'commission_order_outline_id' => 'COM' . getOrderNumber(),
            'product_attribute_uuid' => $order->product_attribute_uuid,
            'product_uuid' => $order->product_uuid,
            'qty' => $order['qty'],
            'status' => $status,
            'price' => $order['price'],
            'total_price' => $order['price'] * $order['qty'],
            'commission' => $product_attribute->profit_four * $order['qty'],
            'producer_uuid' => $retail->producer_uuid,
            'dealer_uuid' => $retail->dealer_uuid,
            'region_uuid' => $retail->region_uuid,
            'channel_uuid' => $retail->channel_uuid,
            'outline_type'=>4,
            'site_id' => $order['site_id'],
            'create_time' => now_time(time()),
            'update_time' => now_time(time())
        ]);
    }

}
