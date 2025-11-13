<?php

namespace app\api\logic\cms;

use app\api\model\Admin;
use app\api\model\AdminLog;
use app\api\model\AdminRole;
use app\api\model\AdminToken;
use app\api\model\Channel;
use app\api\model\Dealer;
use app\api\model\Dictionary;
use app\api\model\Partner;
use app\api\model\Producer;
use app\api\model\Region;
use app\api\model\Retail;
use app\common\tools\wechatPay;
use think\Exception;
use think\Db;

/**
 * 出品方逻辑
 */
class ProducerLogic
{
    static public function getMenu()
    {
        return '系统设置-线下分润账号管理-出品方';
    }

    static public function cmsList($request, $userInfo)
    {
        $where = [
            'p.is_deleted'=>1,
        ];
        $request['status']?$where['p.status'] = $request['status']:'';
        $request['keyword']?$where['p.name|p.contact_name|a.uname'] = ['like', '%' . $request['keyword'] . '%']:'';
        if($userInfo['outline_type'] == 1){
            $where['p.uuid'] = $userInfo['producer_uuid'];
        }
        $result = Producer::build()
            ->field('
                p.*,
                a.uname,
                a.last_login,
                (SELECT COUNT(*) FROM dealer WHERE producer_uuid = p.uuid and is_deleted = 1) + 
                (SELECT COUNT(*) FROM region WHERE producer_uuid = p.uuid and is_deleted = 1) + 
                (SELECT COUNT(*) FROM channel WHERE producer_uuid = p.uuid and is_deleted = 1) AS num
            ')
            ->alias('p')
            ->join('admin a','a.uuid = p.admin_uuid','left')
            ->where($where)
            ->order('p.create_time asc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
        AdminLog::build()->add($userInfo['uuid'], self::getMenu(), '查看列表');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = Producer::build()->where('uuid', $id)->where('is_deleted', 1)->findOrFail();
        $admin = Admin::build()->where('uuid', $data->admin_uuid)->find();
        $data->uname = $admin->uname;
        $data->last_login = $admin->last_login;
        $data->num = Dealer::build()->where('producer_uuid',$id)->where('is_deleted',1)->count();
        $data->num2 =  Region::build()->where('producer_uuid',$id)->where('is_deleted',1)->count() + Channel::build()->where('producer_uuid',$id)->where('is_deleted',1)->count();
        $data->team = $data->num + $data->num2;
        AdminLog::build()->add($userInfo['uuid'], self::getMenu(), '查看详情');
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            Db::startTrans();
            $admin_uuid = uuid();
            $producer_uuid = uuid();
            $admin = [
                'uuid' => $admin_uuid,
                'uname'=>$request['uname'],
                'name'=>$request['name'],
                'phone'=>$request['phone'],
                'password' => md6($request['password']),
                'producer_uuid'=>$producer_uuid,
                'outline_type'=>1,
                'role_uuid'=>[AdminRole::build()->where('name','联合出品方')->value('uuid')],
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
            ];

            $data = [
                'uuid'=>$producer_uuid,
                'name'=>$request['name'],
                'contact_name'=>$request['contact_name'],
                'phone'=>$request['phone'],
                'address'=>$request['address'],
                'address_detail'=>$request['address_detail'],
                'note'=>$request['note'],
                'recommend_name'=>$request['recommend_name'],
                'bank_number'=>$request['bank_number'],
                'bank'=>$request['bank'],
                'site_id'=>$request['site_id'],
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

            Producer::build()->save($data);

            if($request['partner_uuid']){
                Partner::build()->whereIn('uuid',$request['partner_uuid'])->update(['producer_uuid'=>$producer_uuid]);
            }
            AdminLog::build()->add($userInfo['uuid'], self::getMenu(), '新增-' . $request['name']);
            Db::commit();
            return $producer_uuid;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo)
    {
        try {
            Db::startTrans();
            $admin = ['uname'=>$request['uname']];
            $request['password']?$admin['password'] = md6($request['password']):'';
            $is = Admin::build()->where('producer_uuid', $request['uuid'])->where('outline_type',1)->where('is_deleted',1)->findOrFail();
            $is->save($admin);
            if($request['partner_uuid']){
                //先删除，后更新
                Partner::build()->where('producer_uuid',$request['uuid'])->update(['producer_uuid'=>'']);
                Partner::build()->whereIn('uuid',$request['partner_uuid'])->update(['producer_uuid'=>$request['uuid']]);
            }
            unset($request['partner_uuid']);
            unset($request['password']);
            unset($request['uname']);
            $user = Producer::build()->where('uuid', $request['uuid'])->where('is_deleted',1)->findOrFail();
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
            Producer::build()->whereIn('uuid', $id)->update(['is_deleted' => 2,'update_time' => now_time(time())]);
            //删除对应管理员
            Admin::build()->whereIn('producer_uuid',$id)->where('outline_type',1)->update(['is_deleted'=>2,'update_time'=>now_time(time())]);
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
        $banner = Producer::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
        $banner->save(['status' => $request['status']]);
        AdminLog::build()->add($userInfo['uuid'], self::getMenu(), '设置状态');
        return true;
    }
}
