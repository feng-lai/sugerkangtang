<?php

namespace app\api\logic\cms;

use app\api\model\Admin;
use app\api\model\Channel;
use app\api\model\CommissionOrder;
use app\api\model\AdminLog;
use app\api\model\CommissionOrderOutline;
use app\api\model\Dealer;
use app\api\model\Order;
use app\api\model\Partner;
use app\api\model\PartnerOrder;
use app\api\model\PartnerOrderOutline;
use app\api\model\Producer;
use app\api\model\Product;
use app\api\model\ProductAttribute;
use app\api\model\Region;
use app\api\model\Retail;
use app\api\model\User;
use think\Exception;
use think\Db;

/**
 * 2+1推广统计-逻辑
 */
class PartnerOrderStatLogic
{
    static public function menu()
    {
        return '合伙人管理-推广统计';
    }

    static public function stat($request, $userInfo)
    {
        try {
            $data = PartnerOrder::build()
                ->field('
                    (select IFNULL(sum(commission),0) as commission from partner_order where status = 1 and site_id = ' . $request['site_id'] . ') as pending_commission,
                    (select IFNULL(sum(commission),0) as commission from partner_order where status = 2 and site_id = ' . $request['site_id'] . ') as settled_commission,
                    (select sum(wallet) as wallet from retail where site_id = ' . $request['site_id'] . ') as cash_out_commission,
                    (select IFNULL(sum(real_price),0) as wallet from cash_out where status = 1 and site_id = ' . $request['site_id'] . ') as pending_cash_out,
                    (select IFNULL(sum(real_price),0) as wallet from cash_out where status = 2 and site_id = ' . $request['site_id'] . ') as cash_out,
                    (select sum(commission) as commission from partner_order_outline where site_id = ' . $request['site_id'] . ') as outline,
                    (select count(1) as num from partner where is_deleted =1 and type = 2 and site_id = ' . $request['site_id'] . ') as senior_partner,
                    (select count(1) as num from partner where is_deleted =1 and type = 1 and site_id = ' . $request['site_id'] . ') as partner,
                    (select sum(commission) as commission from partner_order where site_id = ' . $request['site_id'] . ') as all_commission,
                    (select count(DISTINCT order_id) as num from partner_order where site_id = ' . $request['site_id'] . ') as order_num
                ')
                ->where('site_id', $request['site_id'])
                ->find();
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '统计数据');
            return $data;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function ranking_order($request, $userInfo)
    {
        try {
            if (!$request['user_uuid']) {
                return ['msg' => 'user_uuid不能为空'];
            }
            if (!$request['order_type']) {
                return ['msg' => 'order_type不能为空'];
            }
            Partner::build()->where('user_uuid', $request['user_uuid'])->where('is_deleted',1)->findOrFail();
            $where = ['site_id' => $request['site_id'], 'is_deleted' => 1, 'status' => 2,'user_uuid'=>$request['user_uuid']];
            $request['order_type'] == 1 ? $where['type'] = ['<>', 3] : $where['level'] = 3;
            $data = PartnerOrder::build()
                ->field('
                    order_id,
                    status,
                    total_price,
                    qty,
                    price,
                    commission,
                    product_uuid,
                    product_attribute_uuid
                ')
                ->where($where)
                ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function ($item) {
                    $product = Product::build()->where('uuid', $item['product_uuid'])->find();
                    $attribute = ProductAttribute::build()
                        ->field('a.name,pa.attribute_value,pa.img as product_attribute_img')
                        ->alias('pa')
                        ->join('attribute a', 'a.uuid = pa.attribute_uuid', 'left')
                        ->where('pa.uuid', $item['product_attribute_uuid'])
                        ->find();
                    $order = Order::build()->where('order_id', $item['order_id'])->find();
                    $item['order_time'] = $order->create_time;
                    $item['order_status'] = $order->status;
                    $item['attribute_name'] = $attribute->name;
                    $item['attribute_value'] = $attribute->attribute_value;
                    $item['product_name'] = $product->name;
                    $item['product_img'] = $attribute->product_attribute_img;
                });
            return $data;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }


    static public function outline_order($request, $userInfo)
    {
        try {
            $where = ['site_id' => $request['site_id'], 'is_deleted' => 1, 'status' => 2];
            Producer::build()->where('uuid', $request['producer_uuid'])->findOrFail();
            $where['producer_uuid'] = $request['producer_uuid'];
            $data = PartnerOrderOutline::build()
                ->field('
                    order_id,
                    status,
                    total_price,
                    qty,
                    price,
                    commission,
                    product_uuid,
                    product_attribute_uuid
                ')
                ->where($where)
                ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function ($item) use ($request) {
                    $product = Product::build()->where('uuid', $item['product_uuid'])->find();
                    $attribute = ProductAttribute::build()
                        ->field('a.name,pa.attribute_value,pa.img as product_attribute_img')
                        ->alias('pa')
                        ->join('attribute a', 'a.uuid = pa.attribute_uuid', 'left')
                        ->where('pa.uuid', $item['product_attribute_uuid'])
                        ->find();
                    $order = Order::build()->where('order_id', $item['order_id'])->find();
                    if ($order) {
                        $item['order_time'] = $order->create_time;
                        $item['order_status'] = $order->status;
                    } else {
                        $item['order_time'] = '';
                        $item['order_status'] = '';
                    }
                    if ($attribute) {
                        $item['attribute_name'] = $attribute->name;
                        $item['attribute_value'] = $attribute->attribute_value;
                        $item['product_img'] = $attribute->product_attribute_img;
                    } else {
                        $item['attribute_name'] = '';
                        $item['attribute_value'] = '';
                        $item['product_img'] = '';
                    }
                    if ($product) {
                        $item['product_name'] = $product->name;
                    } else {
                        $item['product_name'] = '';
                    }
                });
            return $data;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function ranking($request, $userInfo)
    {
        try {
            $where = [
                'r.site_id' => $request['site_id'],
                'r.is_deleted' => 1,
            ];
            $request['type'] ? $where['r.type'] = $request['type'] : '';
            $map = '';
            if ($request['start_time'] && $request['end_time']) {
                $time = checkDateFormat($request['start_time']);
                if ($time && $time == 1) {
                    $map = ' and (create_time between "' . $request['start_time'] . '"  and "' . $request['end_time'] . ' 23:59:59")';
                }
                if ($time && $time == 2) {
                    $map = ' and (create_time between "' . $request['start_time'] . '-01"  and "' . get_end_time($request['end_time']) . '")';
                }
            }

            $order = ['commission', 'desc'];
            $data = Partner::build()
                ->alias('r')
                ->field('
                r.uuid,
                r.name,
                r.type,
                r.user_uuid,
                (select img from user where user.uuid = r.user_uuid) as img,
                
                
                (select IFNULL(sum(commission),0) as commission from partner_order where 
                type <> 3 and 
                status = 2 and 
                user_uuid = r.user_uuid and 
                site_id = ' . $request['site_id'] . $map . ') as commission,
                
                (select IFNULL(sum(commission),0) as commission from partner_order where
                 type = 3 and 
                 status = 2 and 
                 user_uuid = r.user_uuid and 
                 site_id = ' . $request['site_id'] . $map . ') as indirect_commission,
                 
                (select IFNULL(sum(commission),0) as commission from partner_order where 
                status = 2 and 
                user_uuid = r.user_uuid and 
                site_id = ' . $request['site_id'] . $map . ') as total_commission
            ');
            $data = $data->where($where);
            $data = $data->order($order[0], $order[1])
                ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function ($item) {
                    $item['num'] = count(Partner::build()->getAll($item['user_uuid']));
                });
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '合伙人排名');
            return $data;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function outline($request, $userInfo)
    {
        try {
            $where = [
                'r.site_id' => $request['site_id'],
                'r.is_deleted' => 1,
            ];

            $map = '';
            if ($request['start_time'] && $request['end_time']) {
                $time = checkDateFormat($request['start_time']);
                if ($time && $time == 1) {
                    $map = ' and (create_time between "' . $request['start_time'] . '"  and "' . $request['end_time'] . ' 23:59:59")';
                }
                if ($time && $time == 2) {
                    $map = ' and (create_time between "' . $request['start_time'] . '-01"  and "' . get_end_time($request['end_time']) . '")';
                }
            }
            $order = ['commission', 'desc'];

            $data = Producer::build();
            $field = 'producer_uuid';
            if ($userInfo['outline_type'] == 1) {
                $where['uuid'] = $userInfo['producer_uuid'];
            }

            $data = $data
                ->alias('r')
                ->field('
                r.uuid as ' . $field . ',
                name,
                (select IFNULL(sum(commission),0) as commission from partner_order_outline where 
                ' . $field . ' = r.uuid and
                status = 2 and
                site_id = ' . $request['site_id'] . $map . ') as commission,
                (select count(1) as num from partner_order_outline where  
                ' . $field . ' = r.uuid and
                status = 2 and
                site_id = ' . $request['site_id'] . $map . ') as num
            ')
                ->where($where)
                ->order($order[0], $order[1])
                ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function ($item) use ($field) {
                    $item->img = Admin::build()->where($field, $item[$field])->where('outline_type', 1)->value('img');
                });
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '分润统计');
            return $data;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Delete($uuid, $userInfo)
    {
        try {
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '删除收货地址');
            CommissionOrder::build()->whereIn('uuid', explode(',', $uuid))->update(['is_deleted' => 2]);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function setDefault($uuid, $userInfo)
    {
        try {
            $data = CommissionOrder::build()->where('uuid', $uuid)->findOrFail();
            $data->save(['is_default' => 1]);
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '设置默认收货地址');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

}
