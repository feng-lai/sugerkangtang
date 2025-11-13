<?php

namespace app\api\logic\cms;

use app\api\model\Admin;
use app\api\model\Channel;
use app\api\model\CommissionOrder;
use app\api\model\AdminLog;
use app\api\model\CommissionOrderOutline;
use app\api\model\Dealer;
use app\api\model\Order;
use app\api\model\Producer;
use app\api\model\Product;
use app\api\model\ProductAttribute;
use app\api\model\Region;
use app\api\model\Retail;
use app\api\model\User;
use think\Exception;
use think\Db;

/**
 * 推广统计-逻辑
 */
class CommissionOrderStatLogic
{
    static public function menu()
    {
        return '推广管理-推广统计';
    }

    static public function stat($request, $userInfo)
    {
        try {
            $data = CommissionOrder::build()
                ->field('
                    (select sum(commission) as commission from commission_order where status = 1 and site_id = ' . $request['site_id'] . ') as pending_commission,
                    (select IFNULL(sum(commission),0) as commission from commission_order where status = 2 and site_id = ' . $request['site_id'] . ') as settled_commission,
                    (select sum(wallet) as wallet from retail where site_id = ' . $request['site_id'] . ') as cash_out_commission,
                    (select IFNULL(sum(real_price),0) as wallet from cash_out where status = 1 and site_id = ' . $request['site_id'] . ') as pending_cash_out,
                    (select IFNULL(sum(real_price),0) as wallet from cash_out where status = 2 and site_id = ' . $request['site_id'] . ') as cash_out,
                    (select sum(commission) as commission from commission_order_outline where site_id = ' . $request['site_id'] . ') as outline,
                    (select count(1) as num from retail where is_deleted =1 and type = 1 and site_id = ' . $request['site_id'] . ') as promoter,
                    (select count(1) as num from retail where is_deleted =1 and type = 2 and site_id = ' . $request['site_id'] . ') as distributor,
                    (select sum(commission) as commission from commission_order where site_id = ' . $request['site_id'] . ') as all_commission,
                    (select count(DISTINCT order_id) as num from commission_order where site_id = ' . $request['site_id'] . ') as order_num
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
            if (!$request['retail_uuid']) {
                return ['msg' => 'retail_uuid不能为空'];
            }
            if (!$request['order_type']) {
                return ['msg' => 'order_type不能为空'];
            }
            Retail::build()->where('uuid', $request['retail_uuid'])->findOrFail();
            $where = ['site_id' => $request['site_id'], 'is_deleted' => 1, 'status' => 2];
            $request['order_type'] == 1 ? $where['level'] = 1 : $where['level'] = ['<>', 1];
            $data = CommissionOrder::build()
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
                ->where('retail_uuid', $request['retail_uuid'])
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
            if (!$request['outline_type']) {
                return ['msg' => 'outline_type不能为空'];
            }
            $where = ['site_id' => $request['site_id'], 'is_deleted' => 1, 'status' => 2, 'outline_type' => $request['outline_type']];
            switch ($request['outline_type']) {
                case '1':
                    if (!$request['producer_uuid']) {
                        return ['msg' => 'producer_uuid不能为空'];
                    }
                    Producer::build()->where('uuid', $request['producer_uuid'])->findOrFail();
                    $where['producer_uuid'] = $request['producer_uuid'];
                    break;
                case '2':
                    if (!$request['dealer_uuid']) {
                        return ['msg' => 'dealer_uuid不能为空'];
                    }
                    Dealer::build()->where('uuid', $request['dealer_uuid'])->findOrFail();
                    $where['dealer_uuid'] = $request['dealer_uuid'];
                    break;
                case '3':
                    if (!$request['region_uuid']) {
                        return ['msg' => 'region_uuid不能为空'];
                    }
                    Region::build()->where('uuid', $request['region_uuid'])->findOrFail();
                    $where['region_uuid'] = $request['region_uuid'];
                    break;
                case '4':
                    if (!$request['channel_uuid']) {
                        return ['msg' => 'channel_uuid不能为空'];
                    }
                    Channel::build()->where('uuid', $request['channel_uuid'])->findOrFail();
                    $where['channel_uuid'] = $request['channel_uuid'];
                    break;
            }
            $data = CommissionOrderOutline::build()
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
                        ->join('attribute a', 'a.uuid = pa.attribute_uuid')
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
                    $total = 0;
                    $item['detail'] = CommissionOrderOutline::build()
                        ->field(['commission', 'outline_type', 'producer_uuid', 'channel_uuid', 'region_uuid', 'dealer_uuid'])
                        ->order('outline_type asc')
                        ->where('order_id', $item['order_id'])
                        ->where('product_attribute_uuid', $item['product_attribute_uuid'])
                        ->where('outline_type', '>=', $request['outline_type'])
                        ->select()->each(function ($items) use (&$total) {
                            $total += $items['commission'];
                            $name = '';
                            switch ($items['outline_type']) {
                                case '1':
                                    $name = Producer::build()->where('uuid', $items['producer_uuid'])->value('name');
                                    break;
                                case '2':
                                    $name = Dealer::build()->where('uuid', $items['dealer_uuid'])->value('name');
                                    break;
                                case '3':
                                    $name = Region::build()->where('uuid', $items['region_uuid'])->value('name');
                                    break;
                                case '4':
                                    $name = Channel::build()->where('uuid', $items['channel_uuid'])->value('name');
                                    break;
                            }
                            $items['name'] = $name;
                            unset($items['producer_uuid']);
                            unset($items['dealer_uuid']);
                            unset($items['region_uuid']);
                            unset($items['channel_uuid']);
                        });
                    $item['total_commission'] = round($total, 2);
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
            $data = Retail::build()
                ->alias('r')
                ->field('
                r.uuid,
                r.name,
                r.type,
                r.review_status,
                r.user_uuid,
                (select img from user where user.uuid = r.user_uuid) as img,
                
                
                (select IFNULL(sum(commission),0) as commission from commission_order where 
                level = 1 and 
                status = 2 and 
                retail_uuid = r.uuid and 
                site_id = ' . $request['site_id'] . $map . ') as commission,
                
                (select IFNULL(sum(commission),0) as commission from commission_order where
                 level <> 1 and 
                 status = 2 and 
                 retail_uuid = r.uuid and 
                 site_id = ' . $request['site_id'] . $map . ') as indirect_commission,
                 
                (select IFNULL(sum(commission),0) as commission from commission_order where 
                status = 2 and 
                retail_uuid = r.uuid and 
                site_id = ' . $request['site_id'] . $map . ') as total_commission
            ');
            $data = $data->where($where);
            $data = $data->order($order[0], $order[1])
                ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function ($item) {
                    $item['num'] = count(User::build()->getAll($item['user_uuid']));
                });
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '分销员排名');
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

            //当前登录账号


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

            switch ($request['outline_type']) {
                case 1:
                    $data = Producer::build();
                    $field = 'producer_uuid';
                    if ($userInfo['outline_type'] == 1) {
                        $where['uuid'] = $userInfo['producer_uuid'];
                    }
                    break;
                case 2:
                    $data = Dealer::build();
                    $field = 'dealer_uuid';
                    if ($userInfo['outline_type'] == 1) {
                        $where['producer_uuid'] = $userInfo['producer_uuid'];
                    }
                    if ($userInfo['outline_type'] == 2) {
                        $where['uuid'] = $userInfo['dealer_uuid'];
                    }
                    break;
                case 3:
                    $data = Region::build();
                    $field = 'region_uuid';
                    if ($userInfo['outline_type'] == 1) {
                        $where['producer_uuid'] = $userInfo['producer_uuid'];
                    }
                    if ($userInfo['outline_type'] == 2) {
                        $where['dealer_uuid'] = $userInfo['dealer_uuid'];
                    }
                    if ($userInfo['outline_type'] == 3) {
                        $where['uuid'] = $userInfo['region_uuid'];
                    }
                    break;
                case 4:
                    $data = Channel::build();
                    $field = 'channel_uuid';
                    if ($userInfo['outline_type'] == 1) {
                        $where['producer_uuid'] = $userInfo['producer_uuid'];
                    }
                    if ($userInfo['outline_type'] == 2) {
                        $where['dealer_uuid'] = $userInfo['dealer_uuid'];
                    }
                    if ($userInfo['outline_type'] == 3) {
                        $where['region_uuid'] = $userInfo['region_uuid'];
                    }
                    if ($userInfo['outline_type'] == 4) {
                        $where['uuid'] = $userInfo['channel_uuid'];
                    }
                    break;
                default:
            }
            $outline_type = $request['outline_type'];
            $data = $data
                ->alias('r')
                ->field('
                r.uuid as ' . $field . ',
                name,
                (select IFNULL(sum(commission),0) as commission from commission_order_outline where 
                outline_type = ' . $request['outline_type'] . ' and 
                ' . $field . ' = r.uuid and
                status = 2 and
                site_id = ' . $request['site_id'] . $map . ') as commission,
                (select count(1) as num from commission_order_outline where 
                outline_type = ' . $request['outline_type'] . ' and 
                ' . $field . ' = r.uuid and
                status = 2 and
                site_id = ' . $request['site_id'] . $map . ') as num
            ')
                ->where($where)
                ->order($order[0], $order[1])
                ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function ($item) use ($field, $outline_type) {
                    $item->img = Admin::build()->where($field, $item[$field])->where('outline_type', $outline_type)->value('img');
                });
            if ($userInfo['outline_type'] && $userInfo['outline_type'] > $request['outline_type']) {
                $data = [
                    'total' => 0,
                    'per_page' => $request['page_size'],
                    'current_page' => $request['page_index'],
                    'last_page' => 0,
                    'data' => []
                ];
            }
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
