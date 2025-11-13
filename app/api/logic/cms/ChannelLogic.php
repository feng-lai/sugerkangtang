<?php

namespace app\api\logic\cms;

use app\api\model\Admin;
use app\api\model\AdminLog;
use app\api\model\AdminRole;
use app\api\model\AdminToken;
use app\api\model\Region;
use app\api\model\Dealer;
use app\api\model\Producer;
use app\api\model\Channel;
use app\api\model\Retail;
use think\Exception;
use think\Db;

/**
 * 渠道商逻辑
 */
class ChannelLogic
{
    static public function getMenu()
    {
        return '系统设置-线下分润账号管理-渠道商';
    }

    static public function cmsList($request, $userInfo)
    {
        $where = [
            'p.is_deleted'=>1,
        ];
        $request['status']?$where['p.status'] = $request['status']:'';
        $request['region_uuid']?$where['p.region_uuid'] = $request['region_uuid']:'';
        $request['keyword']?$where['p.name|p.contact_name|a.uname'] = ['like', '%' . $request['keyword'] . '%']:'';
        if($userInfo['outline_type'] == 4){
            $where['p.uuid'] = $userInfo['channel_uuid'];
        }
        $result = Channel::build()
            ->field('
                    p.*,
                    a.uname,
                    a.last_login,
                    (SELECT COUNT(*) FROM retail WHERE channel_uuid = p.uuid and is_deleted = 1) AS num,
                    (select count(1) FROM commission_order_outline where outline_type = 4 and channel_uuid = p.uuid and is_deleted = 1 and status <> 3) as commission_order_outline_count,
                    (select ifnull(sum(commission),0) FROM commission_order_outline where outline_type = 4 and channel_uuid = p.uuid and is_deleted = 1 and status <> 3) as commission_order_outline_commission
            ')
            ->alias('p')
            ->join('admin a','a.uuid = p.admin_uuid')
            ->where($where)
            ->order('p.create_time asc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function($item){
                $item['outline_type'] = 4;
            });
        AdminLog::build()->add($userInfo['uuid'], self::getMenu(), '查看列表');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = Channel::build()->where('uuid', $id)->where('is_deleted', 1)->findOrFail();
        $admin = Admin::build()->where('uuid', $data->admin_uuid)->find();
        $data->uname = $admin->uname;
        $data->last_login = $admin->last_login;
        $data->last_name = Region::build()->where('uuid',$data['region_uuid'])->value('name');
        AdminLog::build()->add($userInfo['uuid'], self::getMenu(), '查看详情');
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            Db::startTrans();
            $admin_uuid = uuid();
            $save_uuid = uuid();
            $region = Region::build()->where('uuid',$request['region_uuid'])->find();
            $admin = [
                'uuid' => $admin_uuid,
                'uname'=>$request['uname'],
                'name'=>$request['name'],
                'phone'=>$request['phone'],
                'password' => md6($request['password']),
                'dealer_uuid'=>$region['dealer_uuid'],
                'producer_uuid'=>$region['producer_uuid'],
                'region_uuid'=>$request['region_uuid'],
                'channel_uuid'=>$save_uuid,
                'outline_type'=>4,
                'role_uuid'=>[AdminRole::build()->where('name','渠道商')->value('uuid')],
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
            ];

            $data = [
                'uuid'=>$save_uuid,
                'name'=>$request['name'],
                'contact_name'=>$request['contact_name'],
                'phone'=>$request['phone'],
                'address'=>$request['address'],
                'address_detail'=>$request['address_detail'],
                'note'=>$request['note'],
                'bank_number'=>$request['bank_number'],
                'bank'=>$request['bank'],
                'recommend_name'=>$request['recommend_name'],
                'site_id'=>$request['site_id'],
                'producer_uuid'=>$region['producer_uuid'],
                'dealer_uuid'=>$region['dealer_uuid'],
                'region_uuid'=>$request['region_uuid'],
                'admin_uuid'=>$admin_uuid,
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
            ];

            Admin::build()->save($admin);
            //添加token
            $token = AdminToken::build();
            $token->uuid = uuid();
            $token->admin_uuid = $admin_uuid;
            $token->create_time = now_time(time());
            $token->update_time = now_time(time());
            $token->save();

            Channel::build()->save($data);

            //绑定对应推广员
            if($request['retail_uuid']){
                Retail::build()->whereIn('uuid',$request['retail_uuid'])->update([
                    'channel_uuid'=>$save_uuid,
                    'dealer_uuid'=>$region['dealer_uuid'],
                    'producer_uuid'=>$region['producer_uuid'],
                    'region_uuid'=>$request['region_uuid'],
                ]);
            }

            AdminLog::build()->add($userInfo['uuid'], self::getMenu(), '新增-' . $request['name']);
            Db::commit();
            return $save_uuid;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo)
    {
        try {
            Db::startTrans();
            $region = Region::build()->where('uuid',$request['region_uuid'])->find();
            $admin = ['uname'=>$request['uname']];
            if($region){
                $admin['dealer_uuid'] = $region['dealer_uuid'];
                $admin['producer_uuid'] = $region['producer_uuid'];
                $admin['region_uuid'] = $request['region_uuid'];
                $request['producer_uuid'] = $region['producer_uuid'];
                $request['dealer_uuid'] = $region['dealer_uuid'];
            }
            $request['password']?$admin['password'] = md6($request['password']):'';
            $is = Admin::build()->where('channel_uuid', $request['uuid'])->where('outline_type',4)->where('is_deleted',1)->findOrFail();
            $is->save($admin);
            unset($request['password']);
            unset($request['uname']);

            $user = Channel::build()->where('uuid', $request['uuid'])->where('is_deleted',1)->findOrFail();


            //先解除绑定
            Retail::build()->where('channel_uuid',$request['uuid'])->update(['channel_uuid'=>'','producer_uuid'=>'','dealer_uuid'=>'','region_uuid'=>'']);
            //绑定对应推广员
            if($request['retail_uuid']){
                Retail::build()->whereIn('uuid',$request['retail_uuid'])->update([
                    'channel_uuid'=>$request['uuid'],
                    'dealer_uuid'=>$region?$region['dealer_uuid']:'',
                    'producer_uuid'=>$region?$region['producer_uuid']:'',
                    'region_uuid'=>$request['region_uuid'],
                ]);
            }
            unset($request['retail_uuid']);
            $user->save($request);

            AdminLog::build()->add($userInfo['uuid'], self::getMenu(), '编辑-' . $user['name']);
            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id, $userInfo)
    {
        try {
            Db::startTrans();
            Channel::build()->whereIn('uuid', $id)->update(['is_deleted' => 2,'update_time' => now_time(time())]);
            //删除对应管理员
            Admin::build()->whereIn('channel_uuid',$id)->where('outline_type',4)->update(['is_deleted'=>2,'update_time'=>now_time(time())]);
            //解除对应推广员绑定
            Retail::build()->whereIn('channel_uuid',$id)->update(['channel_uuid'=>'','producer_uuid'=>'','dealer_uuid'=>'','region_uuid'=>'']);
            AdminLog::build()->add($userInfo['uuid'], self::getMenu(), '删除');
            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function setStatus($request, $userInfo)
    {
        $banner = Channel::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
        $banner->save(['status' => $request['status']]);
        AdminLog::build()->add($userInfo['uuid'], self::getMenu(), '设置状态');
        return true;
    }
}
