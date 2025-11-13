<?php

namespace app\api\logic\mini;

use app\api\model\Config;
use app\api\model\Invoice;
use app\api\model\InvoiceTitle;
use app\api\model\Order;
use app\api\model\OrderDetail;
use think\Exception;
use think\Db;

/**
 * 发票抬头-逻辑
 */
class InvoiceLogic
{
    static public function Add($request, $userInfo)
    {
        try {
            //开票设置是否可以开票
            if(Config::build()->where('key','IsInvoice')->value('value') == 2){
                return ['msg'=>'开票已关闭'];
            }
            $order = Order::build()->where(['order_id'=>$request['order_id'],'user_uuid'=>$userInfo['uuid'],'is_deleted'=>1])->findOrFail();
            if($order->status == 1 || $order->status == 5){
                return ['msg'=>'待付款和已取消的订单不能申请开票'];
            }
            if(Invoice::build()->where(['order_id'=>$request['order_id'],'user_uuid'=>$userInfo['uuid'],'site_id'=>$request['site_id'],'is_deleted'=>1])->where('status','<>',3)->find()){
                return ['msg'=>'重复申请'];
            }
            $request['uuid'] = uuid();
            $request['price'] = $order->price;
            $request['user_uuid'] = $userInfo['uuid'];
            $request['invoice_id'] = 'Tx'.getOrderNumber();
            $request['create_time'] = now_time(time());
            $request['update_time'] = now_time(time());
            Invoice::build()->save($request);
            return $request['uuid'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Edit($request, $userInfo)
    {
        try {
            $data = Invoice::build()->where('uuid', $request['uuid'])->where('user_uuid', $userInfo['uuid'])->where('is_deleted', 1)->findOrFail();
            if($data->status != 1){
                return ['msg'=>'非待开票状态不能修改'];
            }
            $request['status'] = 1;
            $request['update_time'] = now_time(time());
            $data->save($request);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }


    static public function List($request, $userInfo)
    {
        try {
            $where = [
                'is_deleted' => 1,
                'site_id' => $request['site_id'],
                'user_uuid' => $userInfo['uuid'],
            ];
            $request['type'] ? $where['type'] = $request['type'] : '';
            return Invoice::build()->where($where)->order('create_time desc')->select();
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Detail($uuid, $userInfo)
    {
        try {
            $data =  Invoice::build()->where('is_deleted', 1)->where('user_uuid', $userInfo['uuid'])->where('uuid', $uuid)->findOrFail();
            $data->order =  OrderDetail::build()
                ->field('o.product_attribute_uuid,o.product_uuid,p.name,o.price,o.qty,att.name as attribute_name,at.attribute_value,at.img')
                ->alias('o')
                ->join('product p','p.uuid = o.product_uuid','left')
                ->join('product_attribute at','at.uuid = o.product_attribute_uuid','left')
                ->join('attribute att','att.uuid = at.attribute_uuid','left')
                ->where('o.order_id',$data['order_id'])
                ->select();
            return $data;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Delete($uuid, $userInfo)
    {
        try {
            $data = Invoice::build()->where('uuid', $uuid)->where('user_uuid', $userInfo['uuid'])->where('is_deleted', 1)->findOrFail();
            $data->save(['is_deleted' => 2]);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

}
