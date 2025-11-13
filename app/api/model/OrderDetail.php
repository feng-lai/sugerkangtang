<?php

namespace app\api\model;

/**
 * 订单详情-模型
 */
class OrderDetail extends BaseModel
{
    public static function build()
    {
        return new self();
    }
    //订单结算
    public function setCommissionOrder($order_id){
        //订单如果不允许售后且配置设置了SettlementType=2就自动完成结算
        //线上分销奖励
        //获取分销层级
        $config_level = Config::build()->where('key', 'level')->value('value');
        $order_detail = OrderDetail::where('order_id', $order_id)->select();
        foreach ($order_detail as $v) {
            //商品分销了 自己买自己分享的不算
            if ($v->invite_uuid && $v->user_uuid != $v->invite_uuid) {
                //绑定关系
                $order_user = User::where('uuid', $v->user_uuid)->find();
                if(!$order_user->invite_uuid){
                    $order_user->save(['invite_uuid' => $v->invite_uuid]);
                }
                $status = 1;
                //不允许售后自动结算
                if($v->is_after_sale == 2){
                    $status = 2;
                }
                $product_attribute = ProductAttribute::where('uuid', $v->product_attribute_uuid)->find();
                if(!$product_attribute){
                    return false;
                }
                //当前层
                $retail = Retail::build()->where('user_uuid', $v->invite_uuid)->find();
                if(!$retail){
                    return false;
                }
                $user = User::where('uuid', $retail->user_uuid)->find();
                if ($retail['type'] == 1) {
                    $commission = $product_attribute->promoter_one;
                } else {
                    $commission = $product_attribute->dealer_one;
                }

                $commission_order = [
                    'uuid' => uuid(),
                    'order_id' => $order_id,
                    'user_uuid' => $retail->user_uuid,
                    'retail_uuid' => $retail->uuid,
                    'commission_order_id' => 'CM' . getOrderNumber(),
                    'product_attribute_uuid' => $v->product_attribute_uuid,
                    'product_uuid' => $v->product_uuid,
                    'qty' => $v['qty'],
                    'status' => $status,
                    'price' => $v['price'],
                    'total_price' => $v['price'] * $v['qty'],
                    'commission' => $commission * $v['qty'],
                    'level' => 1,
                    'site_id' => $v['site_id'],
                    'create_time' => now_time(time()),
                    'update_time' => now_time(time())
                ];

                //线上分销
                CommissionOrder::build()->save($commission_order);

                //线下分销
                if($retail->channel_uuid) {
                    CommissionOrder::build()->settlement_outline($v, $retail, $status, $product_attribute);
                }

                //结算
                //不允许售后的话就自动结算
                if($v->is_after_sale == 2){
                    CommissionOrder::build()->settlement($commission_order);
                }

                $pid = $user['invite_uuid'];
                /**
                if(!$pid){
                    //如果没有上级直接判断线下分销
                    if($retail->channel_uuid){
                        //线下分销
                        CommissionOrder::build()->settlement_outline($v,$retail,$status,$product_attribute);
                    }
                    return true;
                }
                 * **/
                for ($i=2; $i<=$config_level; $i++)
                {
                    if(!$pid){
                        return true;
                    }
                    //上层
                    $user = User::build()->where('uuid', $pid)->find();
                    if(!$user){
                        return true;
                    }

                    $pid = $user['invite_uuid'];

                    $retail = Retail::build()->where('user_uuid', $user->uuid)->find();

                    if(!$retail){
                        return true;
                    }
                    if($retail->channel_uuid){
                        //线下分销
                        CommissionOrder::build()->settlement_outline($v,$retail,$status,$product_attribute);
                    }
                    /**
                    if($i == $config_level){
                        if($retail->channel_uuid){
                            //线下分销
                            CommissionOrder::build()->settlement_outline($v,$retail,$status,$product_attribute);
                        }
                    }**/


                    switch ($i) {
                        case 2:
                            if ($retail['type'] == 1 || ($retail['type'] == 2 && $retail->review_status != 2)) {
                                $commission = $product_attribute->promoter_two;
                            } else {
                                $commission = $product_attribute->dealer_two;
                            }
                            break;
                        case 3:
                            if ($retail['type'] == 1 || ($retail['type'] == 2 && $retail->review_status != 2)) {
                                $commission = $product_attribute->promoter_three;
                            } else {
                                $commission = $product_attribute->dealer_three;
                            }
                            break;
                        case 4:
                            if ($retail['type'] == 1 || ($retail['type'] == 2 && $retail->review_status != 2)) {
                                $commission = $product_attribute->promoter_four;
                            } else {
                                $commission = $product_attribute->dealer_four;
                            }
                            break;
                        default:
                            break;
                    }

                    $commission_order = [
                        'uuid' => uuid(),
                        'order_id' => $order_id,
                        'user_uuid' => $retail->user_uuid,
                        'retail_uuid' => $retail->uuid,
                        'commission_order_id' => 'CM' . getOrderNumber(),
                        'product_attribute_uuid' => $v->product_attribute_uuid,
                        'product_uuid' => $v->product_uuid,
                        'qty' => $v['qty'],
                        'status' => $status,
                        'price' => $v['price'],
                        'total_price' => $v['price'] * $v['qty'],
                        'commission' => $commission * $v['qty'],
                        'level' => $i,
                        'site_id' => $v['site_id'],
                        'create_time' => now_time(time()),
                        'update_time' => now_time(time())
                    ];
                    //线上分销
                    CommissionOrder::build()->save($commission_order);

                    //结算
                    //不允许售后自动结算
                    if($v->is_after_sale == 2){
                        CommissionOrder::build()->settlement($commission_order);
                    }

                }
            }
        }
    }

    //合伙人订单结算
    public function setPartnerOrder($order_id){
        $order_detail = OrderDetail::where('order_id', $order_id)->select();
        foreach ($order_detail as $v) {
            //商品分销了 自己买自己分享的不算
            if ($v->invite_partner_uuid && $v->user_uuid != $v->invite_partner_uuid) {
                $product_attribute = ProductAttribute::where('uuid', $v->product_attribute_uuid)->find();
                if (!$product_attribute) {
                    return false;
                }
                //直推
                $partner = Partner::build()->where('user_uuid', $v->invite_partner_uuid)->find();
                if (!$partner) {
                    return false;
                }

                if ($partner['type'] == 1) {
                    //合伙人
                    $commission = $product_attribute->partner;
                    $type = 2;
                } else {
                    //高级合伙人
                    $commission = $product_attribute->senior_partner;
                    $type = 1;
                }

                $commission_order = [
                    'uuid' => uuid(),
                    'order_id' => $order_id,
                    'user_uuid' => $partner->user_uuid,
                    'partner_uuid' => $partner->uuid,
                    'partner_order_id' => 'PM' . getOrderNumber(),
                    'product_attribute_uuid' => $v->product_attribute_uuid,
                    'product_uuid' => $v->product_uuid,
                    'qty' => $v['qty'],
                    'status' => 2,
                    'type'=>$type,
                    'price' => $v['price'],
                    'total_price' => $v['price'] * $v['qty'],
                    'commission' => $commission * $v['qty'],
                    'level' => 1,
                    'site_id' => $v['site_id'],
                    'create_time' => now_time(time()),
                    'update_time' => now_time(time())
                ];

                //2+1直推订单
                PartnerOrder::build()->save($commission_order);

                //直推直接结算
                PartnerOrder::build()->settlement($commission_order);

                //线下分销
                if ($partner->producer_uuid) {
                    PartnerOrder::build()->settlement_outline($v, $partner, 2, $product_attribute);
                }


                //间推
                $user = User::build()->where('uuid', $partner->user_uuid)->find();
                $data = User::build()->getLast($user->invite_partner_uuid);
                if ($data) {
                    foreach ($data as $k => $val) {
                        if ($val['type'] == 2) {
                            //第一个高级合伙人间推
                            $commission_order['uuid'] = uuid();
                            $commission_order['user_uuid'] = $val['uuid'];
                            $partner = Partner::build()->where('user_uuid', $val['uuid'])->find();
                            $commission_order['partner_uuid'] = $partner->uuid;
                            $commission_order['commission'] = $product_attribute->partner_two * $v['qty'];
                            $commission_order['level'] = 2;
                            $commission_order['type'] = 3;
                            $commission_order['status'] = 1;
                            //2+1分销间推订单
                            PartnerOrder::build()->save($commission_order);
                            break;
                        }
                    }
                }
            }
        }
    }

}
