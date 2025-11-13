<?php

namespace app\api\logic\cms;

use app\api\model\Channel;
use app\api\model\AdminLog;
use app\api\model\Dealer;
use app\api\model\PartnerOrderOutline;
use app\api\model\Producer;
use app\api\model\ProductAttribute;
use app\api\model\Region;
use think\Exception;
use think\Db;

/**
 * 2+1线下分润订单-逻辑
 */
class PartnerOrderOutlineLogic
{
    static public function menu()
    {
        return '系统管理-线下分润账号管理';
    }


    static public function List($request, $userInfo)
    {
        try {
            $where = [
                'c.is_deleted' => 1,
                'c.site_id' => $request['site_id'],
            ];
            $request['producer_uuid'] ? $where['c.producer_uuid'] = $request['producer_uuid'] : '';
            $request['name'] ? $where['u.name|r.name'] = ['like', '%' . $request['name'] . '%'] : '';
            $request['status'] ? $where['c.status'] = $request['status'] : '';
            $request['order_id'] ? $where['o.order_id'] = $request['order_id'] : '';
            ($request['start_time'] && $request['end_time']) ? $where['o.create_time'] = ['between', [$request['start_time'], get_end_time($request['end_time'])]] : '';
            $data = PartnerOrderOutline::build()
                ->alias('c')
                ->join('order o', 'o.order_id = c.order_id', 'left')
                ->join('user u', 'u.uuid = o.user_uuid', 'left')
                ->join('partner r', 'r.uuid = c.partner_uuid', 'left')
                ->field('c.uuid,c.order_id,c.qty,c.total_price,c.commission,c.producer_uuid,c.status,c.product_attribute_uuid,o.create_time,o.status as order_status')
                ->where($where)
                ->order('c.create_time desc')
                ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function ($item) {
                    $outline_name = Producer::build()->field('name,phone')->where(['uuid' => $item['producer_uuid']])->find();
                    $item['outline_name'] = $outline_name['name'];
                    $item['outline_phone'] = $outline_name['phone'];
                    $product = ProductAttribute::build()
                        ->field('a.name as attribute_name,p.name as product_name,pt.attribute_value,pt.img as product_img')
                        ->alias('pt')
                        ->join('product p', 'p.uuid = pt.product_uuid', 'left')
                        ->join('attribute a', 'a.uuid = pt.attribute_uuid', 'left')
                        ->where('pt.uuid', $item['product_attribute_uuid'])
                        ->find();
                    $item['product_name'] = $product->product_name;
                    $item['attribute_name'] = $product->attribute_name;
                    $item['attribute_value'] = $product->attribute_value;
                    $item['product_img'] = $product->product_img;
                });
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '2+1线下分润明细');
            return $data;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

}
