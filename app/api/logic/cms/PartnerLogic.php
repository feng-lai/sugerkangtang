<?php

namespace app\api\logic\cms;

use app\api\model\Admin;
use app\api\model\AdminLog;
use app\api\model\CashOut;
use app\api\model\Channel;
use app\api\model\CommissionOrder;
use app\api\model\Order;
use app\api\model\Partner;
use app\api\model\PartnerOrder;
use app\api\model\Producer;
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
 * 合伙人管理-逻辑
 */
class PartnerLogic
{
    static public function menu()
    {
        return '合伙人管理-合伙人管理';
    }

    static public function cmsList($request, $userInfo)
    {
        $map['is_deleted'] = 1;
        $request['keyword'] ? $map['name|Partner_sn'] = ['like', '%' . $request['keyword'] . '%'] : '';
        $request['start_time'] ? $map['create_time'] = ['between', [$request['start_time'], $request['end_time']]] : '';
        $request['site_id'] ? $map['site_id'] = $request['site_id'] : '';
        $request['producer_uuid'] ? $map['producer_uuid'] = $request['producer_uuid'] : '';
        $request['type'] ? $map['type'] = $request['type'] : '';
        $request['status'] ? $map['status'] = $request['status'] : '';
        $result = Partner::build()
            ->field(['uuid', 'partner_sn', 'user_uuid', 'producer_uuid', 'address', 'address_detail', 'name', 'phone', 'type', 'create_time', 'status', 'note'])
            ->where($map)
            ->order('create_time desc');
        if($request['is_producer'] == 1){
            $result = $result->where(function($query) use ($request){
                $query->where('producer_uuid is not null')
                    ->where('producer_uuid','<>','');
            });
        }
        if($request['is_producer'] == 2){
            $result = $result->where(function($query) use ($request){
                $query->where('producer_uuid is null')
                    ->whereOr('producer_uuid','=','');
            });
        }
        $result = $result->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function ($item) {
                $item->user = User::build()->field('name,img')->where('uuid', $item['user_uuid'])->find();
                $item->p_name = User::build()->where('uuid', User::build()->where('uuid', $item['user_uuid'])->value('invite_partner_uuid'))->value('name');
                $item->num = User::build()->where('invite_partner_uuid', $item['user_uuid'])->count();
                $item->num2 = count(User::build()->getAllIndirectSubordinates($item['user_uuid'], 'invite_partner_uuid'));
                $item->wallet = Retail::build()->where('uuid', $item['user_uuid'])->value('wallet');
                $item->cash_out = CashOut::build()->where('user_uuid', $item['uuid'])->sum('real_price');
                $item->producer_name = Producer::build()->where('uuid', $item['producer_uuid'])->value('name');
            });
        AdminLog::build()->add($userInfo['uuid'], self::menu(), '列表');
        return $result;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            Db::startTrans();
            $user = User::build()->where('phone', $request['phone'])->where('is_deleted', 1)->findOrFail();
            //判断是否已经有用户了
            if (Partner::build()->where('user_uuid', $user['uuid'])->where('is_deleted', 1)->count()) {
                return ['msg' => '该号码的用户已经是合伙人或者高级合伙人了'];
            }
            if ($request['producer_uuid']) {
                Producer::build()->where('uuid', $request['producer_uuid'])->findOrFail();
            }
            $request['uuid'] = uuid();
            $request['create_time'] = now_time(time());
            $request['update_time'] = now_time(time());
            $request['admin_uuid'] = $userInfo['uuid'];
            $request['type'] = 2;
            $request['user_uuid'] = $user['uuid'];
            Partner::build()->insert($request);

            //同步推广员对应提现规则
            $data = [];
            if ($request['cash_out_persent']) {
                $data['cash_out_persent'] = $request['cash_out_persent'];
            }
            if ($request['cash_out_low']) {
                $data['cash_out_low'] = $request['cash_out_low'];
            }
            $retail = Retail::build()->where('user_uuid', $user['uuid'])->find();
            if ($retail) {
                $retail->save($data);
            } else {
                Retail::build()->save([
                    'uuid' => uuid(),
                    'user_uuid' => $user['uuid'],
                    'name' => $user['name'] ? $user['name'] : 'TKT' . getNumberOne(6),
                    'phone' => $user['phone'],
                    'cash_out_persent' => $data['cash_out_persent'],
                    'cash_out_low' => $data['cash_out_low'],
                    'type' => 1,
                    'site_id' => $request['site_id'],
                    'create_time' => now_time(time()),
                    'update_time' => now_time(time()),
                ]);
            }
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '新增-' . $request['name']);
            Db::commit();
            return $request['uuid'];
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

    static function cmsEdit($request, $userInfo)
    {
        try {
            Db::startTrans();
            $data = Partner::build()->where(['uuid' => $request['uuid']])->where('is_deleted', 1)->findOrFail();
            $request['update_time'] = now_time(time());
            $request['user_uuid'] = User::build()->where('phone', $request['phone'])->where('is_deleted', 1)->value('uuid');
            $data->save($request);

            //同步推广员对应提现规则
            $data = [];
            if ($request['cash_out_persent']) {
                $data['cash_out_persent'] = $request['cash_out_persent'];
            }
            if ($request['cash_out_low']) {
                $data['cash_out_low'] = $request['cash_out_low'];
            }
            Retail::build()->where('user_uuid', $request['user_uuid'])->update($data);
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
        $result = Partner::build()
            ->alias('p')
            ->join('retail r', 'p.user_uuid = r.user_uuid and r.is_deleted = 1', 'left')
            ->join('producer pr', 'p.producer_uuid = pr.uuid', 'left')
            ->field(['p.*', 'r.sign_status', 'r.sign_url', 'pr.name as producer_name'])
            ->where(['p.uuid' => $id])
            ->where('p.is_deleted', 1)
            ->findOrFail();
        $result->admin_name = Admin::build()->where('uuid', $result['admin_uuid'])->value('name');
        $user = User::build()->where('uuid', $result['user_uuid'])->where('is_deleted', 1)->find();
        $result->last_name = User::build()->where('uuid', $user['invite_partner_uuid'])->where('is_deleted', 1)->value('name');
        $result->user_name = $user->name;
        $result->user_img = $user->img;
        $result->num = User::build()
            ->alias('u')
            ->join('partner p', 'p.user_uuid = u.uuid', 'left')
            ->where(function ($query) use ($result) {
                $query->whereOr('p.type',null)
                    ->whereOr('p.type',2);
            })
            ->where('u.invite_partner_uuid', $result['user_uuid'])
            ->count();
        $result->num2 = count(Partner::build()->getAllIndirectSubordinates($result['user_uuid']));
        $result->team = $result->num + $result->num2;
        $result->stat = [
            'all' => PartnerOrder::build()->where('user_uuid', $result['user_uuid'])->where('status', 2)->sum('commission'),
            'pending' => PartnerOrder::build()->where('user_uuid', $result['user_uuid'])->where('status', 1)->sum('commission'),
            'cash_out' => Retail::build()->where('user_uuid', $result['user_uuid'])->sum('wallet'),
            'withdrawn' => CashOut::build()->where('user_uuid', $result['user_uuid'])->where('status', 2)->sum('price'),
        ];
        AdminLog::build()->add($userInfo['uuid'], self::menu(), '详情');

        return $result;
    }

    static public function setStatus($request, $userInfo)
    {
        $data = Partner::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
        $res = ['status' => $request['status'], 'admin_uuid' => $userInfo['uuid']];
        $data->save($res);
        AdminLog::build()->add($userInfo['uuid'], self::menu(), '设置状态');
        return true;
    }


    static public function setType($request, $userInfo)
    {
        $data = Partner::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
        $data->save(['type' => $request['type']]);
        AdminLog::build()->add($userInfo['uuid'], self::menu(), '设置类型');
        return true;
    }

    static public function setNote($request, $userInfo)
    {
        $data = Partner::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
        $data->save(['note' => $request['note']]);
        AdminLog::build()->add($userInfo['uuid'], self::menu(), '设置备注');
        return true;
    }

    static public function setReviewStatus($request, $userInfo)
    {
        if ($request['review_status'] == 2) {
            $res = [
                'review_status' => 2,
                'type' => 2,
                'certificate' => $request['certificate'],
                'review_time' => now_time(time()),
                'update_time' => now_time(time()),
            ];
            $request['cash_out_persent'] ? $res['cash_out_persent'] = $request['cash_out_persent'] : '';
            $request['cash_out_low'] ? $res['cash_out_low'] = $request['cash_out_low'] : '';
        } else {
            $res = [
                'review_status' => 3,
                'type' => 1,
                'note' => $request['note'],
                'review_time' => now_time(time()),
                'update_time' => now_time(time()),
            ];
        }
        $data = Partner::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
        $data->save($res);
        AdminLog::build()->add($userInfo['uuid'], self::menu(), '设置备注');
        return true;
    }

    static public function member($id)
    {
        try {
            $partner = Partner::build()->where('uuid', $id)->findOrFail();
            $data = User::build()->getFullTree2($partner->user_uuid);
            return $data;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id, $userInfo)
    {
        try {
            $data = Partner::build()->where('uuid', $id)->findOrFail();
            $data->save(['is_deleted' => 2]);
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '删除');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
