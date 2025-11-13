<?php

namespace app\api\logic\mini;

use app\api\model\CashOut;
use app\api\model\CommissionOrder;
use app\api\model\Config;
use app\api\model\Retail;
use app\api\model\User;
use app\common\tools\ESign;
use think\Exception;

/**
 * 用户信息-逻辑
 */
class RetailLogic
{
    static public function miniList($userInfo)
    {
        // 用户信息
        $result = Retail::build()->field('
            uuid,
            name,
            type,
            phone,
            create_time,
            address,
            address_detail,
            bank_name,
            bank_number,
            business_license,
            protocol,
            certificate,
            status,
            wallet,
            review_status,
            note,
            cash_out_persent,
            cash_out_low,
            site_id,
            contact_name,
            flow_id,
            sign_status
        ')->where('user_uuid', $userInfo['uuid'])
            ->find();

        if($result->sign_status == 2){
            if($result->flow_id){
                $sign_status = ESign::build()->queryFlowDetail($result->flow_id);
                if($sign_status == 2){
                    $result->sign_status = 1;
                    $result->save(['sign_status'=>1]);
                }
            }
        }
        if($result->review_status != 2){
            $result['type'] = 1;
        }
        if(!$result){
            return $result;
        }
        if($userInfo['invite_uuid']){
            $invite = User::build()->where('uuid',$userInfo['invite_uuid'])->find();
            if($invite){
                $result->invite_name = $invite->name;
                $result->invite_img = $invite->img;
            }

        }else{
            $result->invite_name = '';
            $result->invite_img = '';
        }
        $result->total_commission = CommissionOrder::build()->where('user_uuid',$userInfo['uuid'])->where('status',2)->sum('commission');
        $result->pendding_commission = CommissionOrder::build()->where('user_uuid',$userInfo['uuid'])->where('status',1)->sum('commission');
        $result->cash_out = CashOut::build()->where('user_uuid',$userInfo['uuid'])->where('status',2)->sum('price');
        $cash_out_log = CashOut::build()->where('user_uuid',$userInfo['uuid'])->count();
        if($cash_out_log > 0){
            $result->is_cash_out = 1;
        }else{
            $result->is_cash_out = 2;
        }
        if(!$result->cash_out_persent){
            $result->cash_out_persent = Config::build()->where('key','CashOutPersent')->value('value');
        }
        if(!$result->cash_out_low){
            $result->cash_out_low = Config::build()->where('key','CashOutMin')->value('value');
        }
        //直推数量
        $result->next_num = User::build()->where('invite_uuid',$userInfo['uuid'])->where('is_deleted',1)->where('site_id',$result->site_id)->count();
        //间接数量
        $result->indirect_num = count(User::build()->getAllIndirectSubordinates($userInfo['uuid']));


        return $result;
    }
    static public function miniSave($request,$userInfo){
        try {
            $retail = Retail::build()->where('user_uuid', $userInfo['uuid'])->where('is_deleted',1)->where('type',1)->findOrFail();
            if($retail['type'] == 2 && $retail->review_status == 2){
                return ['msg'=>'已经是经销商了'];
            }
            $data = [
                "name"=>$request['name'],
                "phone"=>$request['phone'],
                "address"=>$request['address'],
                "address_detail"=>$request['address_detail'],
                "contact_name"=>$request['contact_name'],
                "bank_name"=>$request['bank_name'],
                "bank_number"=>$request['bank_number'],
                "business_license"=>$request['business_license'],
                "protocol"=>$request['protocol'],
                "type"=>1,
                "review_status"=>1,
            ];
            Retail::build()->where('uuid',$request['uuid'])->update($data);
            return true;
        }catch (\Exception $e){
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Team($request,$userInfo)
    {
        try {
            $data = Retail::build()->where('user_uuid',$userInfo['uuid'])->findOrFail();
            $where = [
                'u.is_deleted'=>1,
                'u.site_id'=>$request['site_id'],
            ];
            if($request['type'] == 1){
                $where['u.invite_uuid'] = $userInfo['uuid'];
            }else{
                $uuid = User::build()->getAllIndirectSubordinates($userInfo['uuid']);
                $where['u.uuid'] = ['in',$uuid];
            }
            $request['name']?$where['u.name|ru.name'] = ['like',"%".$request['name']."%"]:'';
            $res = User::build()
                ->alias('u')
                ->join('retail ru','ru.user_uuid=u.uuid','left')
                ->field('u.uuid,u.img,u.name,u.create_time,ru.name as retail_name')
                ->where($where)
                ->paginate(['list_rows'=>$request['page_size'],'page'=>$request['page_index']])
                ->each(function($item) use ($data){
                $item['num'] = CommissionOrder::build()
                     ->alias('co')
                    ->join('order_detail od','od.order_id = co.order_id and od.product_attribute_uuid = od.product_attribute_uuid','left')
                    ->where('co.user_uuid',$data['user_uuid'])
                    ->where('od.user_uuid',$item['uuid'])
                    ->count();
                $item['commission'] = CommissionOrder::build()
                    ->alias('co')
                    ->join('order_detail od','od.order_id = co.order_id and od.product_attribute_uuid = od.product_attribute_uuid','left')
                    ->where('co.user_uuid',$data['user_uuid'])
                    ->where('od.user_uuid',$item['uuid'])
                    ->where('co.status',2)
                    ->sum('co.commission');
                if($item['retail_name']){
                    $item['name'] = $item['retail_name'];
                }
            });
            return $res;
        }catch (\Exception $e){
            throw new Exception($e->getMessage(), 500);
        }
    }
}
