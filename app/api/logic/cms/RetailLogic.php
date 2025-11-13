<?php

namespace app\api\logic\cms;

use app\api\model\Admin;
use app\api\model\AdminLog;
use app\api\model\CashOut;
use app\api\model\Channel;
use app\api\model\CommissionOrder;
use app\api\model\Order;
use app\api\model\Retail;
use app\api\model\User;
use app\api\model\Score;
use app\api\model\UserInterrest;
use app\api\model\UserRelation;
use app\common\tools\ESign;
use app\common\tools\Sync;
use think\Exception;
use think\Db;

/**
 * 推广管理-分销员管理-逻辑
 */
class RetailLogic
{
    static public function menu()
    {
        return '推广管理-分销员管理';
    }

    static public function cmsList($request, $userInfo)
    {
        $map['is_deleted'] = 1;
        $request['keyword'] ? $map['contact_name|phone|uuid|name|retail_sn'] = ['like', '%' . $request['keyword'] . '%'] : '';
        $request['start_time'] ? $map['create_time'] = ['between', [$request['start_time'], $request['end_time']]] : '';
        $request['review_start_time'] ? $map['review_time'] = ['between', [$request['review_start_time'], $request['review_end_time']]] : '';
        $request['site_id'] ? $map['site_id'] = $request['site_id'] : '';
        $request['channel_uuid'] ? $map['channel_uuid'] = $request['channel_uuid'] : '';
        $request['dealer_uuid'] ? $map['dealer_uuid'] = $request['dealer_uuid'] : '';
        $request['region_uuid'] ? $map['region_uuid'] = $request['region_uuid'] : '';
        $request['producer_uuid'] ? $map['producer_uuid'] = $request['producer_uuid'] : '';
        $request['type'] ? $map['type'] = $request['type'] : '';
        $request['status'] ? $map['status'] = $request['status'] : '';
        if($request['is_review_table'] == 1){
            unset($map['review_status']);
        }else{
            $map['review_status'] = ['in',[1,2,3]];
            $request['review_status'] ? $map['review_status'] = $request['review_status'] : '';
        }
        $result = Retail::build();
        if($request['is_channel'] == 1){
            $result = $result->where(function($query) use ($request){
                $query->where('channel_uuid is not null')
                    ->where('channel_uuid','<>','');
            });
        }
        if($request['is_channel'] == 2){
            $result = $result->where(function($query) use ($request){
                $query->where('channel_uuid is null')
                    ->whereOr('channel_uuid','=','');
            });
        }

        $result = $result->where($map);

        //print_r($result->fetchSql(true)->select());exit;
        $result = $result->order('create_time desc')->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function ($item) {
                $item->user = User::build()->field('name,img')->where('uuid', $item['user_uuid'])->find();
                $item->p_name = User::build()->where('uuid', User::build()->where('uuid',$item['user_uuid'])->value('invite_uuid'))->value('name');
                //$result->num = User::build()->where('invite_uuid', $result['user_uuid'])->count();
            //        $result->num2 = count(User::build()->getAllIndirectSubordinates($result['user_uuid']));
                $item->num = User::build()->where('invite_uuid', $item['user_uuid'])->count();
                $item->num2 = count(User::build()->getAllIndirectSubordinates($item['user_uuid']));
                $item->cash_out = CashOut::build()->where('retail_uuid', $item['uuid'])->sum('real_price');
                $item->channel = Channel::build()->where('uuid', $item['channel_uuid'])->value('name');
            });
        AdminLog::build()->add($userInfo['uuid'], self::menu(), '列表');
        return $result;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            $user = User::build()->where('phone', $request['phone'])->where('is_deleted', 1)->findOrFail();
            if($user->uuid == $request['puuid']){
                return ['msg'=>'不能绑定自己为自己上级'];
            }
            //判断是否已经有用户了
            if(Retail::build()->where('user_uuid',$user['uuid'])->where('is_deleted', 1)->count()){
                return ['msg'=>'该号码的用户已经是推广员/经销商'];
            }
            if($request['channel_uuid']){
                $channel = Channel::build()->where('uuid', $request['channel_uuid'])->findOrFail();
                $request['producer_uuid'] = $channel['producer_uuid'];
                $request['dealer_uuid'] = $channel['dealer_uuid'];
                $request['region_uuid'] = $channel['region_uuid'];
            }
            //更新一下user的invite_uuid
            $user->save(['invite_uuid' => $request['puuid']]);

            $request['uuid'] = uuid();
            $request['create_time'] = now_time(time());
            $request['update_time'] = now_time(time());
            $request['admin_uuid'] = $userInfo['uuid'];
            $request['type'] = 2;
            $request['review_status'] = 2;
            $request['wallet'] = 0;
            $request['user_uuid'] = $user['uuid'];
            unset($request['puuid']);
            Retail::build()->insert($request);

            AdminLog::build()->add($userInfo['uuid'], self::menu(), '新增-' . $request['name']);
            return $request['uuid'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static function cmsEdit($request, $userInfo)
    {
        try {
            Db::startTrans();
            $data = Retail::build()->where(['uuid' => $request['uuid']])->where('is_deleted', 1)->findOrFail();
            if($request['channel_uuid']){
                $channel = Channel::build()->where('uuid', $request['channel_uuid'])->findOrFail();
                $request['producer_uuid'] = $channel['producer_uuid'];
                $request['dealer_uuid'] = $channel['dealer_uuid'];
                $request['region_uuid'] = $channel['region_uuid'];
            }
            $request['update_time'] = now_time(time());
            $request['user_uuid'] = User::build()->where('phone', $request['phone'])->where('is_deleted', 1)->value('uuid');

            if($request['user_uuid'] == $request['puuid']){
                return ['msg'=>'不能绑定自己为自己上级'];
            }

            //更新一下user的invite_uuid
            User::build()->where('uuid', $data['user_uuid'])->update(['invite_uuid' => $request['puuid']]);
            unset($request['puuid']);
            $data->save($request);

            Db::commit();
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '编辑-' . $request['name']);
            return true;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDetail($id, $userInfo)
    {

        $result = Retail::build()->where(['uuid' => $id])->where('is_deleted', 1)->findOrFail();

        if($result->sign_status == 2){
            if($result->flow_id){
                $sign_status = ESign::build()->queryFlowDetail($result->flow_id);
                if($sign_status == 2){
                    $result->sign_status = 1;
                    $result->save(['sign_status'=>1]);
                }
            }
        }
        $result->url = $result->sign_url;
        if($result['type'] == 2 && $result->review_status != 2){
            $result['type'] = 1;
        }
        $user = User::build()->where('uuid', $result['user_uuid'])->where('is_deleted', 1)->find();
        $result->last_name = User::build()->where('uuid', $user['invite_uuid'])->where('is_deleted', 1)->value('name');
        $result->user_name = $user->name;
        $result->user_img = $user->img;
        $result->admin_name = Admin::build()->where(['uuid' => $result['admin_uuid']])->value('name');

        $result->num = User::build()->where('invite_uuid', $result['user_uuid'])->count();
        $result->num2 = count(User::build()->getAllIndirectSubordinates($result['user_uuid']));
        $result->team = $result->num+$result->num2;
        $result->stat = [
            'all' => CommissionOrder::build()->where('user_uuid', $result['user_uuid'])->where('status',2)->sum('commission'),
            'pending' => CommissionOrder::build()->where('user_uuid', $result['user_uuid'])->where('status', 1)->sum('commission'),
            'cash_out' => $result->wallet,
            'withdrawn' => CashOut::build()->where('user_uuid', $result['user_uuid'])->where('status', 2)->sum('price'),
        ];

        AdminLog::build()->add($userInfo['uuid'], self::menu(), '详情');

        return $result;
    }

    static public function setStatus($request, $userInfo)
    {
        $data = Retail::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
        $res = ['status' => $request['status'],'admin_uuid'=>$userInfo['uuid']];
        $data->save($res);
        AdminLog::build()->add($userInfo['uuid'], self::menu(), '设置状态');
        return true;
    }


    static public function setType($request, $userInfo)
    {
        $data = Retail::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
        $res = ['type' => $request['type']];
        if($request['type'] == 1){
            //推广员
            $res['review_status'] = 1;
        }
        if($request['type'] == 2){
            //经销商
            $res['review_status'] = 2;
        }
        $data->save($res);
        AdminLog::build()->add($userInfo['uuid'], self::menu(), '设置类型');
        return true;
    }

    static public function setNote($request, $userInfo)
    {
        $data = Retail::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
        $data->save(['note' => $request['note']]);
        AdminLog::build()->add($userInfo['uuid'], self::menu(), '设置备注');
        return true;
    }

    static public function setReviewStatus($request, $userInfo)
    {
        if ($request['review_status'] == 2) {
            $res = [
                'review_status' => 2,
                'type'=>2,
                'certificate' => $request['certificate'],
                'review_time' => now_time(time()),
                'update_time' => now_time(time()),
            ];
            $request['cash_out_persent'] ? $res['cash_out_persent'] = $request['cash_out_persent'] : '';
            $request['cash_out_low'] ? $res['cash_out_low'] = $request['cash_out_low'] : '';
        } else {
            $res = [
                'review_status' => 3,
                'type'=>1,
                'note' => $request['note'],
                'review_time' => now_time(time()),
                'update_time' => now_time(time()),
            ];
        }
        $data = Retail::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
        $data->save($res);
        AdminLog::build()->add($userInfo['uuid'], self::menu(), '设置备注');
        return true;
    }

    static public function member($id)
    {
        try {
            $retail = Retail::build()->where('uuid', $id)->findOrFail();
            $data = User::build()->getFullTree($retail->user_uuid);
            return $data;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
