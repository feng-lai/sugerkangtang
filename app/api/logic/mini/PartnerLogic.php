<?php

namespace app\api\logic\mini;

use app\api\model\CashOut;
use app\api\model\CommissionOrder;
use app\api\model\Config;
use app\api\model\Partner;
use app\api\model\PartnerOrder;
use app\api\model\Retail;
use app\api\model\User;
use app\common\tools\ESign;
use think\Exception;

/**
 * 合伙人-逻辑
 */
class PartnerLogic
{

    static public function Team($request,$userInfo)
    {
        try {
            $data = Partner::build()->where('user_uuid',$userInfo['uuid'])->findOrFail();
            $where = [
                'u.is_deleted'=>1,
                'u.site_id'=>$request['site_id'],
            ];
            if($request['type'] == 1){
                $where['u.invite_partner_uuid'] = $userInfo['uuid'];
            }else{
                $uuid = Partner::build()->getAllIndirectSubordinates($userInfo['uuid']);
                $where['u.uuid'] = ['in',$uuid];
            }
            $request['name']?$where['u.name|ru.name'] = ['like',"%".$request['name']."%"]:'';
            $res = User::build()
                ->alias('u')
                ->join('partner ru','ru.user_uuid=u.uuid','left')
                ->field('u.uuid,u.img,u.name,u.create_time,ru.name as partner_name')
                ->where($where)
                ->where(function ($query) {
                    $query->whereOr('ru.type',null)
                        ->whereOr('ru.type',2);
                })
                ->paginate(['list_rows'=>$request['page_size'],'page'=>$request['page_index']])
                ->each(function($item) use ($data){
                $item['num'] = PartnerOrder::build()
                     ->alias('co')
                    ->join('order_detail od','od.order_id = co.order_id and od.product_attribute_uuid = od.product_attribute_uuid','left')
                    ->where('co.user_uuid',$data['user_uuid'])
                    ->where('od.user_uuid',$item['uuid'])
                    ->count();
                $item['commission'] = PartnerOrder::build()
                    ->alias('co')
                    ->join('order_detail od','od.order_id = co.order_id and od.product_attribute_uuid = od.product_attribute_uuid','left')
                    ->where('co.user_uuid',$data['user_uuid'])
                    ->where('od.user_uuid',$item['uuid'])
                    ->where('co.status',2)
                    ->sum('co.commission');
                if($item['partner_name']){
                    $item['name'] = $item['partner_name'];
                }
            });
            return $res;
        }catch (\Exception $e){
            throw new Exception($e->getMessage(), 500);
        }
    }
}
