<?php

namespace app\api\logic\cms;

use app\api\model\Admin;
use app\api\model\AdminLog;
use app\api\model\AdminRole;
use app\api\model\AdminToken;
use app\api\model\Channel;
use app\api\model\Dealer;
use app\api\model\Producer;
use app\api\model\Region;
use app\api\model\Retail;
use think\Exception;
use think\Db;

/**
 * 特邀经销商逻辑
 */
class DealerLogic
{
    static public function getMenu()
    {
        return '系统设置-线下分润账号管理-特邀经销商';
    }

    static public function cmsList($request, $userInfo)
    {
        $where = [
            'p.is_deleted' => 1,
        ];
        $request['status'] ? $where['p.status'] = $request['status'] : '';
        $request['producer_uuid'] ? $where['p.producer_uuid'] = $request['producer_uuid'] : '';
        $request['keyword'] ? $where['p.name|p.contact_name|a.uname'] = ['like', '%' . $request['keyword'] . '%'] : '';
        if($userInfo['outline_type'] == 2){
            $where['p.uuid'] = $userInfo['dealer_uuid'];
        }
        $result = Dealer::build()
            ->field('
                p.*,a.uname,
                a.last_login,
                (select count(1) FROM commission_order_outline where outline_type = 2 and dealer_uuid = p.uuid and is_deleted = 1 and status <> 3) as commission_order_outline_count,
                (select sum(commission) FROM commission_order_outline where outline_type = 2 and dealer_uuid = p.uuid and is_deleted = 1 and status <> 3) as commission_order_outline_commission,
                (SELECT COUNT(*) FROM region WHERE dealer_uuid = p.uuid and is_deleted = 1) + 
                (SELECT COUNT(*) FROM channel WHERE dealer_uuid = p.uuid and is_deleted = 1) AS num
            ')
            ->alias('p')
            ->join('admin a', 'a.uuid = p.admin_uuid')
            ->where($where)
            ->order('p.create_time asc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function ($item) {
                $item['outline_type'] = 2;
                $item['child'] = Region::build()
                    ->alias('p')
                    ->field('
                    uuid,
                    name,
                    phone,
                    create_time,
                    (SELECT COUNT(*) FROM channel WHERE region_uuid = p.uuid and is_deleted = 1) AS num,
                    (select count(1) FROM commission_order_outline where outline_type = 3 and region_uuid = p.uuid and is_deleted = 1 and status <> 3) as commission_order_outline_count,
                    (select ifnull(sum(commission),0) FROM commission_order_outline where outline_type = 3 and region_uuid = p.uuid and is_deleted = 1 and status <> 3) as commission_order_outline_commission
                ')
                    ->where('dealer_uuid', $item['uuid'])
                    ->select()
                    ->each(function ($item) {
                        $item['outline_type'] = 3;
                        $item['child'] = Channel::build()
                            ->alias('p')
                            ->field('
                                uuid,
                                name,
                                phone,
                                create_time,
                                (SELECT COUNT(*) FROM retail WHERE channel_uuid = p.uuid and is_deleted = 1) AS num,
                                (select count(1) FROM commission_order_outline where outline_type = 4 and channel_uuid = p.uuid and is_deleted = 1 and status <> 3) as commission_order_outline_count,
                                (select ifnull(sum(commission),0) FROM commission_order_outline where outline_type = 4 and channel_uuid = p.uuid and is_deleted = 1 and status <> 3) as commission_order_outline_commission
                            ')
                            ->where('region_uuid', $item['uuid'])
                            ->select()
                            ->each(function ($item) {
                                $item['outline_type'] = 4;
                            });
                    });
            });
        AdminLog::build()->add($userInfo['uuid'], self::getMenu(), '查看列表');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = Dealer::build()->where('uuid', $id)->where('is_deleted', 1)->findOrFail();
        $admin = Admin::build()->where('uuid', $data->admin_uuid)->find();
        $data->uname = $admin->uname;
        $data->last_login = $admin->last_login;
        $data->last_name = Producer::build()->where('uuid', $data['producer_uuid'])->value('name');
        $data->num = Region::build()->where('dealer_uuid', $id)->where('is_deleted', 1)->count();
        $data->num2 = Channel::build()->where('dealer_uuid', $id)->where('is_deleted', 1)->count();
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
                'uname' => $request['uname'],
                'name' => $request['name'],
                'phone' => $request['phone'],
                'password' => md6($request['password']),
                'dealer_uuid' => $producer_uuid,
                'producer_uuid' => $request['producer_uuid'],
                'outline_type' => 2,
                'role_uuid' => [AdminRole::build()->where('name', '特邀经销商')->value('uuid')],
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
            ];

            $data = [
                'uuid' => $producer_uuid,
                'name' => $request['name'],
                'contact_name' => $request['contact_name'],
                'phone' => $request['phone'],
                'address' => $request['address'],
                'address_detail' => $request['address_detail'],
                'note' => $request['note'],
                'recommend_name'=>$request['recommend_name'],
                'bank_number' => $request['bank_number'],
                'bank' => $request['bank'],
                'site_id' => $request['site_id'],
                'producer_uuid' => $request['producer_uuid'],
                'admin_uuid' => $admin_uuid,
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

            Dealer::build()->save($data);
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
            $admin = ['uname' => $request['uname']];
            $admin['producer_uuid'] = $request['producer_uuid'];
            $request['password'] ? $admin['password'] = md6($request['password']) : '';
            $is = Admin::build()->where('dealer_uuid', $request['uuid'])->where('outline_type', 2)->where('is_deleted', 1)->findOrFail();
            $is->save($admin);
            unset($request['password']);
            unset($request['uname']);
            $user = Dealer::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
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
            Dealer::build()->whereIn('uuid', $id)->update(['is_deleted' => 2, 'update_time' => now_time(time())]);
            //删除对应管理员
            Admin::build()->whereIn('dealer_uuid', $id)->where('outline_type', 2)->update(['is_deleted' => 2, 'update_time' => now_time(time())]);
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
        $banner = Dealer::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
        $banner->save(['status' => $request['status']]);
        AdminLog::build()->add($userInfo['uuid'], self::getMenu(), '设置状态');
        return true;
    }
}
