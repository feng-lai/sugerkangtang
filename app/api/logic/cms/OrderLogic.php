<?php

namespace app\api\logic\cms;

use app\api\controller\SmsReport;
use app\api\model\Admin;
use app\api\model\AdminLog;
use app\api\model\AfterSale;
use app\api\model\AfterSaleDetail;
use app\api\model\Config;
use app\api\model\MedicalReport;
use app\api\model\Msg;
use app\api\model\Order;
use app\api\model\OrderAddress;
use app\api\model\OrderDetail;
use app\api\model\OrderLog;
use app\api\model\OrderPath;
use app\api\model\User;
use think\Exception;
use think\Db;

/**
 * 报名逻辑
 * User:
 * Date: 2022-08-11
 * Time: 21:24
 */
class OrderLogic
{
    static public function menu()
    {
        return '订单管理-订单列表';
    }

    static public function cmsList($request, $userInfo)
    {
        $map = [];
        $request['keyword'] ? $map['o.order_id|p.name'] = ['like', '%' . $request['keyword'] . '%'] : '';
        $request['user_info'] ? $map['a.name|a.phone'] = ['like', '%' . $request['user_info'] . '%'] : '';
        $request['status'] ? $map['o.status'] = ['=', $request['status']] : '';
        $request['user_uuid'] ? $map['o.user_uuid'] = ['=', $request['user_uuid']] : '';
        $request['start_time'] ? $map['o.create_time'] = ['between', [$request['start_time'], $request['end_time']]] : '';
        $result = Order::build()
            ->field('
                o.uuid,
                o.order_id,
                o.price,
                o.create_time,
                o.status,
                o.medical_report_uuid,
                o.note,
                o.order_address_uuid,
                o.com,
                o.num,
                o.com_name,
                u.name,
                u.img,
                a.name as address_name,
                a.phone,
                a.province,
                a.city,
                a.district,
                a.address,
                m.status as medical_report_status
            ')
            ->alias('o')
            ->join('order_address a', 'a.uuid = o.order_address_uuid', 'LEFT')
            ->join('medical_report m', 'm.uuid = o.medical_report_uuid', 'LEFT')
            ->join('user u', 'u.uuid = o.user_uuid', 'LEFT')
            ->join('order_detail od', 'od.order_id = o.order_id', 'left')
            ->join('product p', 'p.uuid = od.product_uuid', 'LEFT')
            ->group('o.order_id')
            ->where($map)->order('o.create_time desc')->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function ($item) {
                $item['product'] = OrderDetail::build()
                    ->field('o.product_attribute_uuid,o.product_uuid,p.name,o.price,o.qty,att.name as attribute_name,at.attribute_value,at.img')
                    ->alias('o')
                    ->join('product p', 'p.uuid = o.product_uuid', 'left')
                    ->join('product_attribute at', 'at.uuid = o.product_attribute_uuid', 'left')
                    ->join('attribute att', 'att.uuid = at.attribute_uuid', 'left')
                    ->where('o.order_id', $item['order_id'])
                    ->select();
                $need_medical_report = OrderDetail::build()->where('order_id', $item['order_id'])->where('is_after_sale', 1)->find();
                if ($need_medical_report) {
                    $item['need_medical_report'] = 1;
                } else {
                    $item['need_medical_report'] = 2;
                }
            });
        AdminLog::build()->add($userInfo['uuid'], self::menu(), '查询列表');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = Order::build()->where('order_id', $id)->findOrFail();
        $left_time = 0;
        if($data->status == 1){
            $left_time = strtotime($data->create_time) + Config::build()->where('key','OrderCancelTime')->value('value')*3600 - time();
        }
        $data['left_time'] = $left_time;
        $data->after_sale_stauts = AfterSale::build()->where('order_id', $id)->value('status');
        $data->order_log = OrderLog::build()->where('order_id', $id)->order('create_time asc')->select()->each(function ($item) {
            $item['admin_name'] = Admin::build()->where('uuid', $item->admin_uuid)->value('name');
        });
        $data->adddress = OrderAddress::build()->where('uuid', $data['order_address_uuid'])->find();
        $data->user = User::build()->field('name,phone')->where('uuid', $data['user_uuid'])->find();
        $data->medical_report = MedicalReport::build()->where('uuid', $data['medical_report_uuid'])->find();

        $data['product'] = OrderDetail::build()
            ->field('o.product_attribute_uuid,o.product_uuid,p.name,o.price,o.qty,att.name as attribute_name,at.attribute_value,at.img,p.code,o.order_id')
            ->alias('o')
            ->join('product p', 'p.uuid = o.product_uuid', 'left')
            ->join('product_attribute at', 'at.uuid = o.product_attribute_uuid', 'left')
            ->join('attribute att', 'att.uuid = at.attribute_uuid', 'left')
            ->where('o.order_id', $id)
            ->select();
        AdminLog::build()->add($userInfo['uuid'], self::menu(), '查询详情');
        return $data;
    }


    static public function cmsDelete($request, $userInfo)
    {
        try {
            $data = Order::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->where('status', 1)->findOrFail();
            $data->save(['reason' => $request['reason'], 'status' => 2, 'cancel_type' => 3]);
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '删除订单');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Shipment($request, $userInfo)
    {
        try {
            Db::startTrans();
            if (!$request['shipment_info']) {
                return ['msg' => '快递信息不能为空'];
            }
            foreach ($request['shipment_info'] as $key => $value) {
                $order = Order::build()->where('order_id', $value['order_id'])->find();
                if ($order && $order->status == 2) {
                    $order->save([
                        'status' => 3,
                        'com' => $value['com'],
                        'num' => $value['num'],
                        'com_name' => $value['com_name'],
                        'ship_time' => now_time(time()),
                    ]);
                    //更新物流
                    OrderPath::build()->getPath($order);

                    Order::build()->ship($order);

                    //订单记录
                    OrderLog::build()->save([
                        'uuid' => uuid(),
                        'order_id' => $value['order_id'],
                        'name'=>'订单发货',
                        'admin_uuid' => $userInfo['uuid'],
                        'content'=>['物流公司'=>$value['com_name'],'快递单号'=>$value['num']],
                        'site_id'=>$order['site_id'],
                        'create_time' => now_time(time()),
                        'update_time' => now_time(time()),
                    ]);
                }
            }
            Db::commit();
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '发货');
            return true;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function setNote($request, $userInfo)
    {
        try {
            $order = Order::build()->where('order_id', $request['order_id'])->findOrFail();
            $order->save(['admin_note' => $request['note'], 'update_time' => now_time(time())]);
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '设置备注');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function setPrice($request, $userInfo)
    {
        try {

            if (!$request['product'] || !is_array($request['product'])) {
                return ['msg' => '商品非法参数'];
            }
            if (count($request['product']) != OrderDetail::build()->where('order_id', $request['order_id'])->count()) {
                return ['msg' => '商品非法参数'];
            }
            $order = Order::build()->where('order_id', $request['order_id'])->findOrFail();
            $price = 0;
            foreach ($request['product'] as $key => $value) {
                $detail = OrderDetail::build()->where('order_id', $request['order_id'])->where('product_attribute_uuid', $value['product_attribute_uuid'])->findOrFail();
                $detail->save(['price' => $value['price'], 'update_time' => now_time(time())]);
                $price = $price + $value['price'] * $detail->qty;
            }
            Order::build()->where('order_id', $request['order_id'])->update(['price' => $price]);
            //订单日志
            OrderLog::build()->save([
                'uuid' => uuid(),
                'order_id' => $request['order_id'],
                'name' => '修改价格',
                'content' => ['修改后合计' => $price],
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s'),
            ]);
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '修改价格');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cancel($request, $userInfo)
    {
        try {
            Db::startTrans();
            $data = Order::build()->where('order_id', $request['order_id'])->findOrFail();
            if (!in_array($data->status, [1, 2,4])) {
                return ['msg' => '待付款/待发货/已完成的订单才能取消'];
            }
            //是否有售后
            $is = AfterSale::build()->where('order_id', $request['order_id'])->where('is_deleted',1)->where('status',1)->find();
            if($is){
                return ['msg'=>'售后状态订单不能关闭'];
            }
            //退款
            $res = Order::build()->refund($data, -$data->price, '取消订单');
            if (!$res) {
                return ['msg'=>'退款失败'];
            }
            $data->save(['status' => 5, 'cancel_time' => date('Y-m-d H:i:s'), 'reason' => $request['reason']]);
            Order::build()->cancel($request);
            //订单日志
            OrderLog::build()->save([
                'uuid' => uuid(),
                'order_id' => $request['order_id'],
                'name' => '订单关闭',
                'admin_uuid' => $userInfo['uuid'],
                'content' => ['取消原因' => $request['reason']],
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s'),
            ]);
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '修改价格');
            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function order_count($userInfo)
    {
        try {
            $statusList = [1, 2, 3, 4, 5];
            $result = Order::build()
                ->field('status,count(*) as count')
                ->group('status')
                ->select()->toArray();

            // 将结果转为以status为key的数组
            $data = array_column($result, null, 'status');

            // 补全缺失的status
            foreach ($statusList as $status) {
                if (!isset($data[$status])) {
                    $data[$status] = ['status' => $status, 'count' => 0];
                }
            }
            $data = array_values($data);
            usort($data, function ($a, $b) {
                return $a['status'] <=> $b['status'];
            });
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '订单状态统计');
            return $data;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
