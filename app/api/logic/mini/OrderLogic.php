<?php

namespace app\api\logic\mini;

use app\api\controller\v1\mini\Cart;
use app\api\model\Address;
use app\api\model\AfterSaleDetail;
use app\api\model\Config;
use app\api\model\Invoice;
use app\api\model\MedicalReport;
use app\api\model\Order;
use app\api\model\OrderAddress;
use app\api\model\OrderLog;
use app\api\model\OrderPath;
use app\api\model\Partner;
use app\api\model\PartnerReview;
use app\api\model\Refund;
use app\api\model\Retail;
use app\api\model\User;
use app\common\tools\WechatRefund;
use app\api\model\OrderDetail;
use app\api\model\Product;
use app\api\model\ProductAttribute;
use think\Exception;
use think\Db;

/**
 * 订单-逻辑
 * User:
 * Date: 2022-07-21
 * Time: 14:31
 */
class OrderLogic
{

    static public function detail($id, $userInfo)
    {
        try {
            $data = Order::build()->field('order_id,status,create_time,note,pay_time,price,medical_report_uuid,order_address_uuid,com,num,com_name')->where('order_id', $id)->where('is_deleted', 1)->findOrFail();
            $data->product = OrderDetail::build()
                ->field('o.product_attribute_uuid,o.product_uuid,p.name,o.price,o.qty,att.name as attribute_name,at.attribute_value,at.img,p.is_after_sale,p.after_sale_day')
                ->alias('o')
                ->join('product p', 'p.uuid = o.product_uuid', 'left')
                ->join('product_attribute at', 'at.uuid = o.product_attribute_uuid', 'left')
                ->join('attribute att', 'att.uuid = at.attribute_uuid', 'left')
                ->where('o.order_id', $data['order_id'])
                ->select();
            $data->address = OrderAddress::build()->where('uuid', $data['order_address_uuid'])->find();
            $data->refund = Refund::build()->where('order_id', $id)->value('price');
            $data->medical_report = MedicalReport::build()->where('uuid', $data['medical_report_uuid'])->find();
            unset($data['order_address_uuid']);
            $left_time = 0;
            if ($data->status == 1) {
                $time = Config::build()->where('key', 'OrderCancelTime')->value('value');

            }
            return $data;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function List($request, $userInfo)
    {
        try {
            $where = [
                'o.user_uuid' => $userInfo['uuid'],
                'o.site_id' => $request['site_id'],
                'o.is_deleted' => 1
            ];
            if ($request['status']) {
                $where['o.status'] = $request['status'];
            }
            if ($request['keyword']) {
                $where['o.order_id|p.name'] = ['like', '%' . $request['keyword'] . '%'];
            }
            if ($request['start_time'] && $request['end_time']) {
                $where['o.create_time'] = ['between time', [$request['start_time'], $request['end_time']]];
            }
            $res = Order::build()
                ->alias('o')
                ->field('o.order_id,o.price,o.medical_report_uuid,o.status,o.create_time,o.num,o.com,o.com_name')
                ->join('order_detail de', 'de.order_id = o.order_id', 'left')
                ->join('product p', 'p.uuid = de.product_uuid', 'left')
                ->where($where)
                ->order('o.create_time DESC')
                ->group('o.order_id')
                ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function ($item) {
                    $item->product = OrderDetail::build()
                        ->field('o.order_id,o.product_attribute_uuid,o.product_uuid,p.name,o.price,o.qty,att.name as attribute_name,at.attribute_value,at.img,p.is_after_sale,p.after_sale_day')
                        ->alias('o')
                        ->join('product p', 'p.uuid = o.product_uuid', 'left')
                        ->join('product_attribute at', 'at.uuid = o.product_attribute_uuid', 'left')
                        ->join('attribute att', 'att.uuid = at.attribute_uuid', 'left')
                        ->where('o.order_id', $item['order_id'])
                        ->select()->each(function ($item) {
                            $is_sale = AfterSaleDetail::build()->alias('ad')->where(['ad.order_id' => $item['order_id'], 'ad.product_attribute_uuid' => $item['product_attribute_uuid'], 'a.status' => ['in', [1, 2]]])->join('after_sale a', 'a.after_sale_id = ad.after_sale_id', 'left')->count();
                            if ($is_sale) {
                                $item->is_sale = 1;
                            } else {
                                $item->is_sale = 2;
                            }
                        });
                    $item->invoice_uuid = Invoice::build()->where('order_id', $item['order_id'])->where('status', 'in', [1, 2])->where('is_deleted', 1)->value('uuid');
                    $item->path = OrderPath::build()->where('order_id', $item['order_id'])->order('time desc')->find();
                });
            return $res;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Add($request, $userInfo)
    {
        try {
            Db::startTrans();
            $order_id = getOrderNumber();
            $time = now_time(time());
            $price = 0;
            foreach ($request['product'] as $v) {
                $data = ProductAttribute::build()->where('uuid', $v['product_attribute_uuid'])->findOrFail();
                $product = Product::build()->where('uuid', $data['product_uuid'])->find();
                if ($product->vis == 2) {
                    return ['msg' => '商品已下架'];
                }
                if ($data->qty < $v['qty']) {
                    return ['msg' => '库存不足'];
                }
                $price += $data['price'] * $v['qty'];
                orderDetail::build()->insert([
                    'uuid' => uuid(),
                    'order_id' => $order_id,
                    'user_uuid' => $userInfo['uuid'],
                    'product_attribute_uuid' => $v['product_attribute_uuid'],
                    'product_uuid' => $data['product_uuid'],
                    'qty' => $v['qty'],
                    'price' => $data['price'],
                    'is_after_sale' => $product['is_after_sale'],
                    'after_sale_day' => $product['after_sale_day'],
                    'invite_uuid' => isset($v['invite_uuid']) ? $v['invite_uuid'] : '',
                    'invite_partner_uuid'=>$userInfo['invite_partner_uuid'],
                    'site_id' => $request['site_id'],
                    'create_time' => $time,
                    'update_time' => $time,
                ]);
                //减库存
                ProductAttribute::build()->where('uuid', $data['uuid'])->setDec('qty', $v['qty']);
                Product::build()->where('uuid', $data['product_uuid'])->setDec('qty', $v['qty']);
                //加销量
                ProductAttribute::build()->where('uuid', $data['uuid'])->setInc('sale', $v['qty']);
                //清购物车
                if ($request['type'] == 2) {
                    \app\api\model\Cart::build()->where('user_uuid', $userInfo['uuid'])->where('product_attribute_uuid', $v['product_attribute_uuid'])->update(['is_deleted' => 2]);
                }
            }

            //订单地址
            $address = Address::build()->where('uuid', $request['address_uuid'])->where('user_uuid', $userInfo['uuid'])->findOrFail();
            $order_address = $address->toArray();
            $order_address['uuid'] = uuid();
            $order_address['create_time'] = $time;
            $order_address['update_time'] = $time;
            unset($order_address['is_default']);
            unset($order_address['user_uuid']);
            OrderAddress::build()->insert($order_address);
            if ($request['medical_report_uuid']) {
                MedicalReport::build()->where('uuid', $request['medical_report_uuid'])->where('user_uuid', $userInfo['uuid'])->findOrFail();
            }

            Order::build()->insert([
                'uuid' => uuid(),
                'order_id' => $order_id,
                'user_uuid' => $userInfo['uuid'],
                'order_address_uuid' => $order_address['uuid'],
                'note' => $request['note'],
                'medical_report_uuid' => $request['medical_report_uuid'],
                'price' => $price,
                'site_id' => $request['site_id'],
                'create_time' => $time,
                'update_time' => $time,
            ]);

            //订单日志
            OrderLog::build()->save([
                'uuid' => uuid(),
                'order_id' => $order_id,
                'name' => '订单已创建',
                'create_time' => $time,
                'update_time' => $time,
                'site_id' => $request['site_id']
            ]);
            if ($request['medical_report_uuid']) {
                //订单日志
                OrderLog::build()->save([
                    'uuid' => uuid(),
                    'order_id' => $order_id,
                    'name' => '上传报告',
                    'create_time' => $time,
                    'update_time' => $time,
                    'site_id' => $request['site_id']
                ]);
            }

            Db::commit();
            return $order_id;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }


    static public function Delete($uuid, $userInfo)
    {
        try {
            $res = Order::build()->where('order_id', $uuid)->where('user_uuid', $userInfo['uuid'])->where('is_deleted', 1)->findOrFail();
            $res->save(['is_deleted' => 2]);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cancel($request, $userInfo)
    {
        try {
            Db::startTrans();
            $data = Order::build()->where('order_id', $request['order_id'])->where('user_uuid', $userInfo['uuid'])->findOrFail();
            if (!in_array($data->status, [1, 2])) {
                return ['msg' => '待付款/待发货的订单才能取消'];
            }
            if ($data->status == 2) {
                //退款
                $res = Order::build()->refund($data, -$data->price, '取消订单');
                if (!$res) {
                    return false;
                }
            }
            $data->save(['status' => 5, 'cancel_time' => date('Y-m-d H:i:s'), 'reason' => $request['reason']]);
            Order::build()->cancel($request);
            //订单日志
            OrderLog::build()->save([
                'uuid' => uuid(),
                'order_id' => $request['order_id'],
                'name' => '取消订单',
                'content' => ['取消原因' => $request['reason']],
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s'),
            ]);
            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function confirm($request, $userInfo)
    {
        try {
            Db::startTrans();
            $data = Order::build()->where('order_id', $request['order_id'])->where('user_uuid', $userInfo['uuid'])->findOrFail();
            if ($data->status != 3) {
                return ['msg' => '运输中的订单才能确认收货'];
            }
            $data->save(['status' => 4, 'confirm_time' => date('Y-m-d H:i:s')]);

            //订单记录
            OrderLog::build()->save([
                'uuid' => uuid(),
                'order_id' => $request['order_id'],
                'name'=>'确认收货',
                'site_id'=>$data['site_id'],
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
            ]);

            //成为推广员
            if (!Retail::build()->where('user_uuid', $userInfo['uuid'])->where('is_deleted', 1)->count()) {
                $res = [
                    'uuid' => uuid(),
                    'user_uuid' => $userInfo['uuid'],
                    'name' => 'TKT' . getNumberOne(6),
                    'site_id' => $userInfo['site_id'],
                    'phone'=>$userInfo['phone'],
                    'create_time' => now_time(time()),
                    'update_time' => now_time(time()),
                ];
                Retail::build()->insert($res);
            }

            //成为合伙人
            if (!Partner::build()->where('user_uuid', $userInfo['uuid'])->where('is_deleted', 1)->count()) {
                $res = [
                    'uuid' => uuid(),
                    'user_uuid' => $userInfo['uuid'],
                    'name' => 'TKT' . getNumberOne(6),
                    'site_id' => $userInfo['site_id'],
                    'phone'=>$userInfo['phone'],
                    'create_time' => now_time(time()),
                    'update_time' => now_time(time()),
                ];
                Partner::build()->insert($res);
                //上级是否够条件成为高级合伙人
                $partner = Partner::build()->where('user_uuid', $userInfo['invite_partner_uuid'])->where('is_deleted', 1)->find();
                if($partner && $partner['type'] == 1){
                    $count = User::build()->where('invite_partner_uuid',$userInfo['invite_partner_uuid'])->where('is_deleted', 1)->count();
                    $num = Config::build()->where('key','BeSeniorPartner')->value('value');
                    if($count >= $num){
                        //申请成为高级合伙人
                        PartnerReview::build()->insert([
                            'uuid' => uuid(),
                            'user_uuid' => $userInfo['invite_partner_uuid'],
                            'create_time' => now_time(time()),
                            'update_time' => now_time(time()),
                            'site_id' => $userInfo['site_id'],
                        ]);
                    }
                }
            }
            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }


    static public function setMedicalReport($request, $userInfo)
    {
        $order = Order::build()->where('order_id', $request['order_id'])->findOrFail();
        $medical_report = MedicalReport::build()->where('uuid', $request['medical_report_uuid'])->findOrFail();
        $order->save(['medical_report_uuid' => $request['medical_report_uuid'], 'update_time' => now_time(time())]);
        return true;
    }

}
