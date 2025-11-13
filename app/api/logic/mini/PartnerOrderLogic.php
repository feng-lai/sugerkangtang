<?php

namespace app\api\logic\mini;

use app\api\model\Config;
use app\api\model\OrderDetail;
use app\api\model\PartnerOrder;
use think\Exception;
use think\Db;

/**
 * 2+1分销订单-逻辑
 */
class PartnerOrderLogic
{



    static public function List($request, $userInfo)
    {
        try {
            $where = [
                'c.is_deleted' => 1,
                'c.site_id' => $request['site_id'],
                'c.user_uuid' => $userInfo['uuid'],
            ];
            $request['status']?$where['c.status'] = $request['status']:'';
            $request['keyword']?$where['u.name|p.name|c.order_id'] = ['like', '%' . $request['keyword'] . '%']:'';
            $data = PartnerOrder::build()
                ->field('
                    c.order_id,
                    c.status,
                    c.commission,
                    c.total_price,
                    c.price,
                    c.qty,
                    u.name as buyer_name,
                    u.img as buyer_img,
                    p.name as product_name,
                    o.status as order_status,
                    o.confirm_time,
                    pa.img as product_img,
                    a.name as attribute_name,
                    pa.attribute_value,
                    c.product_attribute_uuid
                ')
                ->alias('c')
                ->join('product p','p.uuid = c.product_uuid','left')
                ->join('product_attribute pa','pa.uuid = c.product_attribute_uuid','left')
                ->join('attribute a','a.uuid = pa.attribute_uuid','left')
                ->join('order o','o.order_id = c.order_id','left')
                ->join('user u','u.uuid = o.user_uuid','left')
                ->where($where)
                ->order('c.create_time', 'desc')
                ->paginate(['list_rows'=>$request['page_size'],'page'=>$request['page_index']])->each(function($item){
                    $time = '';
                    if(Config::build()->where('key','SettlementType')->value('value')==3 && $item['order_status']==5 && $item['status'] == 1){
                        $time = strtotime($item['confirm_time']) + Config::build()->where('key','SettlementDay')->value('value') * 86400;
                        $time = date('Y-m-d H:i:s',$time);
                    }
                    $order_detail = OrderDetail::build()->where('order_id',$item['order_id'])->where('product_attribute_uuid',$item['product_attribute_uuid'])->find();
                    if(Config::build()->where('key','SettlementType')->value('value')==2 && $item['status'] == 1 && $order_detail->is_after_sale == 1){
                        $time = strtotime($item['confirm_time']) + $order_detail->after_sale_day * 86400;
                        $time = date('Y-m-d H:i:s',$time);
                    }
                    $item['due_time'] = $time;
                });
            return $data;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }


}
