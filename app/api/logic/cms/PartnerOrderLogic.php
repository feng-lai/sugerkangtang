<?php

namespace app\api\logic\cms;

use app\api\model\Bill;
use app\api\model\PartnerOrder;
use app\api\model\AdminLog;
use app\api\model\PartnerOrderOutline;
use app\api\model\Retail;
use TencentCloud\Mrs\V20200910\Models\Part;
use think\Exception;
use think\Db;

/**
 * 2+1分销订单-逻辑
 */
class PartnerOrderLogic
{
    static public function menu()
    {
        return '合伙人管理-推广订单';
    }


    static function Add($request,$userInfo)
    {
        try {
            Db::startTrans();
            $price = 0;
            foreach ($request['parameter'] as $k => $v) {
                $price += $v['commission'];
                $data = PartnerOrder::build()->where('partner_order_id',$v['partner_order_id'])->where('status',1)->findOrFail();
                $total = $data->total_price;
                $data->save(['status'=>$request['status'],'commission'=>$v['commission']]);
                PartnerOrderOutline::build()->where('order_id',$data->order_id)->where('product_attribute_uuid',$data->product_attribute_uuid)->update(['status'=>$request['status']]);
                if($request['status']==2){
                    //钱包
                    $retail = Retail::build()->where('uuid',$data->retail_uuid)->findOrFail();
                    $retail->setInc('wallet',$v['commission']);
                    //账单
                    Bill::build()->insert([
                        'uuid'=>uuid(),
                        'partner_order_id'=>$data->partner_order_id,
                        'price'=>$v['commission'],
                        'site_id'=>$data->site_id,
                        'type'=>2,
                        'bill_id'=>'BX'.getOrderNumber(),
                        'user_uuid'=>$data->user_uuid,
                        'wallet'=>$retail->wallet,
                        'create_time'=>now_time(time()),
                        'update_time'=>now_time(time()),
                    ]);
                }
            }
            if($price > $total){
                return ['msg'=>'佣金总额不能大于订单金额'];
            }
            Db::commit();
            return true;
        }catch (Exception $e){
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Edit($request, $userInfo)
    {
        try {
            $data = PartnerOrder::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
            $request['update_time'] = now_time(time());
            $data->save($request);
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '收货地址编辑');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }


    static public function List($request, $userInfo)
    {
        try {
            $where = [
                'c.is_deleted' => 1,
                'c.site_id' => $request['site_id'],
                'c.level'=>1
            ];
            $request['order_id'] ? $where['c.order_id'] = $request['order_id']:'';
            $request['user_uuid'] ? $where['c.user_uuid'] = $request['user_uuid'] : '';
            $request['id'] ? $where['c.order_id|c.partner_order_id'] = $request['id'] : '';
            $request['name'] ? $where['u.name|r.name'] = ['like', '%' . $request['name'] . '%'] : '';
            $request['status'] ? $where['c.status'] = $request['status'] : '';
            ($request['start_time'] && $request['end_time']) ? $where['od.create_time'] = ['between', [$request['start_time'], get_end_time($request['end_time'])]] : '';
            $data = PartnerOrder::build()
                ->alias('c')
                ->field('
                    c.partner_order_id,
                    c.order_id,
                    c.user_uuid,
                    c.product_attribute_uuid,
                    c.qty,
                    c.price,
                    c.total_price,
                    od.create_time,
                    p.name,
                    oa.img,
                    aa.name as attribute_name,
                    oa.attribute_value,
                    u.name as user_name,
                    u.img as user_img,
                    c.status
                ')
                ->join('order_detail od', ' c.order_id = od.order_id and c.product_attribute_uuid = od.product_attribute_uuid', 'left')
                ->join('product_attribute oa', 'oa.uuid = od.product_attribute_uuid', 'LEFT')
                ->join('product p', 'p.uuid = oa.product_uuid', 'LEFT')
                ->join('attribute aa', 'aa.uuid = oa.attribute_uuid', 'LEFT')
                ->join('user u', 'od.user_uuid = u.uuid', 'left')
                ->join('partner r', 'r.user_uuid = c.user_uuid and r.is_deleted = 1', 'left')
                ->where($where)
                ->order('c.create_time desc')
                ->group('c.order_id,c.product_attribute_uuid')
                ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function ($item) {
                    $total_price = round($item['total_price'],2);
                    $detail = PartnerOrder::build()
                        ->field('c.level,r.name,c.commission,c.type')
                        ->alias('c')
                        ->join('partner r', 'r.user_uuid = c.user_uuid', 'left')
                        ->where('c.order_id', $item['order_id'])
                        ->where('c.product_attribute_uuid', $item['product_attribute_uuid'])
                        ->order('c.type asc')
                        ->select()->each(function ($item) use ($total_price) {
                            $item['type'] = $item['type']+1;
                            $item['persent'] = round($item['commission'] / $total_price * 100,2);
                            unset($item['level']);
                        })->toArray();
                    $producer = PartnerOrderOutline::build()
                        ->alias('p')
                        ->field('pr.name,p.commission')
                        ->join('producer pr','pr.uuid = p.producer_uuid','left')
                        ->where('p.order_id', $item['order_id'])
                        ->where('p.product_attribute_uuid', $item['product_attribute_uuid'])
                        ->find()->toArray();
                    if($producer){
                        $producer['persent'] = round($producer['commission'] / $total_price * 100,2);
                        $producer['type'] = 1;
                        $arr = array_merge([$producer],$detail);
                    }else{
                        $arr = $detail;
                    }
                    $item['detail'] = $arr;
                });
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '查询列表');
            return $data;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Detail($uuid, $userInfo)
    {
        try {
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '查询详情');
            return PartnerOrder::build()->where('is_deleted', 1)->where('uuid', $uuid)->findOrFail();
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Delete($uuid, $userInfo)
    {
        try {
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '删除收货地址');
            PartnerOrder::build()->whereIn('uuid', explode(',', $uuid))->update(['is_deleted' => 2]);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function setDefault($uuid, $userInfo)
    {
        try {
            $data = PartnerOrder::build()->where('uuid', $uuid)->findOrFail();
            $data->save(['is_default' => 1]);
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '设置默认收货地址');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

}
