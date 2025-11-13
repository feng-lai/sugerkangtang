<?php

namespace app\api\logic\mini;

use app\api\model\Address;
use app\api\model\AfterSale;
use app\api\model\AfterSaleDetail;
use app\api\model\AfterSaleLog;
use app\api\model\MedicalReport;
use app\api\model\Order;
use app\api\model\OrderAddress;
use app\api\model\OrderLog;
use app\api\model\Reason;
use app\common\tools\WechatRefund;
use app\api\model\OrderDetail;
use app\api\model\Product;
use app\api\model\ProductAttribute;
use think\Exception;
use think\Db;

/**
 * 售后-逻辑
 */
class AfterSaleLogic
{

    static public function detail($id, $userInfo)
    {
        try {
            $data = AfterSale::build()->where('after_sale_id', $id)->where('is_deleted', 1)->findOrFail();
            $data->product = AfterSaleDetail::build()
                ->field('p.uuid as product_uuid,p.name,a.price,a.qty,av.name as attribute_name,at.attribute_value,at.img as product_img,p.is_after_sale,p.after_sale_day')
                ->alias('a')
                ->join('product_attribute at', 'a.product_attribute_uuid = at.uuid','left')
                ->join('product p', 'at.product_uuid = p.uuid','left')
                ->join('attribute av', 'av.uuid = at.attribute_uuid','left')
                ->where('order_id', $data->order_id)
                ->select();
            $data->log = AfterSaleLog::build()->where('after_sale_id', $id)->where('is_deleted', 1)->order('create_time desc')->select();
            return $data;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function List($request, $userInfo)
    {
        try {
            $where = [
                'a.user_uuid' => $userInfo['uuid'],
                'a.site_id' => $request['site_id'],
                'a.is_deleted' => 1
            ];
            if($request['start_time'] && $request['end_time']){
                $where['a.create_time'] = ['between time', [$request['start_time'], $request['end_time']]];
            }
            if($request['keyword']){
                $where['p.name|a.order_id'] = ['like', '%'.$request['keyword'].'%'];
            }
            $res = AfterSale::build()
                ->alias('a')
                ->join('after_sale_detail av', 'av.after_sale_id = a.after_sale_id', 'left')
                ->join('product p', 'p.uuid = av.product_uuid', 'left')
                ->where($where)
                ->order('a.create_time DESC')
                ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function ($item) {
                    $order_id = $item['order_id'];
                    $item->product = AfterSaleDetail::build()
                        ->field('p.uuid as product_uuid,p.name,a.price,a.qty,av.name as attribute_name,at.attribute_value,at.img as product_img,p.is_after_sale,p.after_sale_day')
                        ->alias('a')
                        ->join('product_attribute at', 'at.uuid = a.product_attribute_uuid','left')
                        ->join('product p', 'at.product_uuid = p.uuid','left')
                        ->join('attribute av', 'av.uuid = at.attribute_uuid','left')
                        ->where('a.order_id', $order_id)
                        ->where('a.is_deleted', 1)
                        ->where('a.after_sale_id',$item['after_sale_id'])
                        ->select();
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
            $after_sale_id = getOrderNumber();
            $order  = Order::build()->where('order_id', $request['order_id'])->where('user_uuid',$userInfo['uuid'])->findOrFail();
            if(!in_array($order->status,[2,3,4])){
                return ['msg'=>'待付款和已取消的订单不能售后'];
            }

            //报告是否有,是否通过审核
            if(!$order->medical_report_uuid){
                return ['msg'=>'报告没上传,不能售后'];
            }

            if(MedicalReport::build()->where('uuid',$order->medical_report_uuid)->value('status') != 2){
                return ['msg'=>'报告无效,不能售后'];
            }
            $product_attribute_uuid = [];
            $price = 0;
            foreach($request['product'] as $v){
                $product_attribute_uuid[] = $v['product_attribute_uuid'];
                $order_detail = OrderDetail::build()->where('product_attribute_uuid',$v['product_attribute_uuid'])->where('order_id',$request['order_id'])->find();
                $price += $order_detail->price * $v['qty'];
                if($v['qty']>$order_detail->qty){
                    return ['msg'=>'退款商品数量不能大于订单商品数量'];
                }
                $product_attribute = ProductAttribute::build()->where('uuid',$v['product_attribute_uuid'])->findOrFail();
                //是否允许售后
                if($order_detail['is_after_sale'] == 2){
                    return ['msg'=>'有不允许售后的商品'];
                }

                //是否已提交售后
                $is = AfterSaleDetail::build()
                    ->alias('a')
                    ->join('after_sale as','as.after_sale_id = a.after_sale_id', 'left')
                    ->where([
                        'a.order_id' => $request['order_id'],
                        'a.product_attribute_uuid' => $v['product_attribute_uuid'],
                        'a.user_uuid' => $userInfo['uuid'],
                        'as.status'=>['in',[1,2]]
                    ])->count();
                if($is){
                    return ['msg'=>'售后正在审核或者已通过'];
                }
            }
            //保存数据
            $data = [
                'user_uuid'=>$userInfo['uuid'],
                'order_id'=>$request['order_id'],
                'reason'=>$request['reason'],
                'more_reason'=>$request['more_reason'],
                'img'=>$request['img'],
                'price'=>$price,
                'after_sale_id'=>$after_sale_id,
                'create_time'=>now_time(time()),
                'update_time'=>now_time(time()),
            ];
            $exist = AfterSale::build()
                ->where([
                    'order_id' => $request['order_id'],
                    'user_uuid' => $userInfo['uuid']
                ])->find();

            if($exist){
                $data['status'] = 1;
                $data['after_sale_id'] = $exist->after_sale_id;
                $exist->save($data);
            }else{
                $data['uuid'] = uuid();
                AfterSale::build()->save($data);
                //新增详情
                foreach ($request['product'] as $v){
                    AfterSaleDetail::build()->save([
                        'uuid'=>uuid(),
                        'order_id'=>$request['order_id'],
                        'product_attribute_uuid'=>$v['product_attribute_uuid'],
                        'qty'=>$v['qty'],
                        'after_sale_id'=>$after_sale_id,
                        'product_uuid'=>ProductAttribute::build()->where('uuid',$v['product_attribute_uuid'])->value('product_uuid'),
                        'user_uuid'=>$userInfo['uuid'],
                        'price'=>OrderDetail::build()->where(['order_id'=>$request['order_id'],'product_attribute_uuid'=>$v['product_attribute_uuid']])->value('price')
                    ]);
                }

            }

            AfterSaleLog::build()->save([
                'uuid'=>uuid(),
                'after_sale_id'=>$data['after_sale_id'],
                'name'=>'提交退款申请',
                'create_time'=>now_time(time()),
                'update_time'=>now_time(time())
            ]);

            //订单日志
            OrderLog::build()->save([
                'uuid' => uuid(),
                'order_id' => $request['order_id'],
                'name'=>'申请售后',
                'content'=>['售后原因'=>$request['reason']],
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s'),
            ]);
            Db::commit();
            return $data['after_sale_id'];
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }


    static public function Delete($uuid, $userInfo)
    {
        try {
            $res = AfterSale::build()->where('after_sale_id', $uuid)->where('user_uuid', $userInfo['uuid'])->where('is_deleted', 1)->findOrFail();
            $res->save(['status' => 4]);
            AfterSaleLog::build()->save([
                'uuid'=>uuid(),
                'after_sale_id'=>$uuid,
                'name'=>'取消售后',
                'create_time'=>now_time(time()),
                'update_time'=>now_time(time()),
            ]);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }



    static public function confirm($request, $userInfo)
    {
        try {
            $data = Order::build()->where('order_id', $request['order_id'])->where('user_uuid', $userInfo['uuid'])->findOrFail();
            if ($data->status != 3) {
                return ['msg' => '运输中的订单才能确认收货'];
            }
            $data->save(['status' => 4]);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }


}
