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
use app\api\model\PartnerReview;
use app\api\model\Producer;
use app\api\model\Retail;
use app\api\model\User;
use app\api\model\Score;
use app\api\model\UserInterrest;
use app\api\model\UserRelation;
use app\common\tools\ESign;
use app\common\tools\Sync;
use Darabonba\GatewaySpi\Models\InterceptorContext\request;
use think\Exception;
use think\Db;

/**
 * 合伙人审核管理-逻辑
 */
class PartnerReviewLogic
{
    static public function menu()
    {
        return '合伙人管理-合伙人审核';
    }

    static public function cmsList($request, $userInfo)
    {
        $map['pr.is_deleted'] = 1;
        $request['keyword'] ? $map['p.name|p.phone|pr.uuid'] = ['like', '%' . $request['keyword'] . '%'] : '';
        $request['start_time'] ? $map['pr.create_time'] = ['between', [$request['start_time'], $request['end_time']]] : '';
        $request['site_id'] ? $map['pr.site_id'] = $request['site_id'] : '';
        $request['review_status'] ? $map['pr.review_status'] = $request['review_status'] : '';
        $request['review_start_time'] ? $map['pr.review_time'] = ['between', [$request['review_start_time'], $request['review_end_time']]] : '';
        $result = PartnerReview::build()
            ->alias('pr')
            ->join('partner p', 'p.user_uuid = pr.user_uuid and p.is_deleted = 1', 'left')
            ->field(['pr.uuid','pr.review_status','p.user_uuid','p.producer_uuid','p.address','p.address_detail','p.name','p.phone','p.type','pr.create_time','pr.note'])
            ->where($map)
            ->order('pr.create_time desc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function ($item) {
                $item->user = User::build()->field('name,img')->where('uuid', $item['user_uuid'])->find();
                $item->p_name = User::build()->where('uuid', User::build()->where('uuid', $item['user_uuid'])->value('invite_partner_uuid'))->value('name');
                $item->num = User::build()->where('invite_partner_uuid', $item['user_uuid'])->count();
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
            if($retail){
                $retail->save($data);
            }else{
                Retail::build()->save([
                    'uuid' => uuid(),
                    'user_uuid' => $user['uuid'],
                    'name' => $user['name']?$user['name']:'TKT' . getNumberOne(6),
                    'phone' => $user['phone'],
                    'cash_out_persent' => $data['cash_out_persent'],
                    'cash_out_low' => $data['cash_out_low'],
                    'type'=>1,
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
            $data = PartnerReview::build()->where(['uuid' => $request['uuid']])->where('is_deleted', 1)->findOrFail();
            $partner = Partner::build()->where('user_uuid', $data['user_uuid'])->where('is_deleted', 1)->findOrFail();
            if($data->review_status != 1){
                return ['msg'=>'非待审核状态'];
            }
            $data->save([
                'review_status' => $request['review_status'],
                'review_time' => now_time(time()),
                'update_time' => now_time(time()),
                'admin_uuid' => $userInfo['uuid'],
                'note' => $request['note'],
            ]);

            if($request['review_status'] == 2){
                $res = ['type' => 2,'update_time' => now_time(time())];
                $request['cash_out_persent']?$res['cash_out_persent'] = $request['cash_out_persent']:'';
                $request['cash_out_low']?$res['cash_out_low'] = $request['cash_out_low']:'';
                $request['protocol']?$res['protocol'] = $request['protocol']:'';
                //通过，更新为高级合伙人
                $partner->save($res);
                //更新推广员提现信息
                if($request['cash_out_persent']){
                    Retail::build()->where('user_uuid', $data['user_uuid'])->where('is_deleted', 1)->update([
                        'cash_out_persent' => $request['cash_out_persent'],
                        'cash_out_low' => $request['cash_out_low'],
                    ]);
                }
            }
            Db::commit();
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '编辑-' . $partner['name']);
            return true;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDetail($id, $userInfo)
    {
        $result = PartnerReview::build()->where(['uuid' => $id])->where('is_deleted', 1)->findOrFail();
        $result->admin_name = Admin::build()->where('uuid', $result['admin_uuid'])->where('is_deleted',1)->value('name');
        $user = User::build()->where('uuid', $result['user_uuid'])->where('is_deleted', 1)->find();
        $result->last_name = User::build()->where('uuid', $user['invite_partner_uuid'])->where('is_deleted', 1)->value('name');
        $result->user_name = $user->name;
        $result->user_img = $user->img;
        $result->num = User::build()->where('invite_partner_uuid', $result['user_uuid'])->where('is_deleted',1)->count();
        $result->wallet = Retail::build()->where('user_uuid', $result['user_uuid'])->where('is_deleted',1)->value('wallet');
        $partner = Partner::build()->where('user_uuid', $result['user_uuid'])->where('is_deleted', 1)->find();
        $result->partner_sn = $partner->partner_sn;
        $result->cash_out_persent = $partner['cash_out_persent'];
        $result->cash_out_low =  $partner['cash_out_low'];
        $result->protocol = $partner['protocol'];
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
        $res = ['type' => $request['type']];
        if ($request['type'] == 1) {
            //推广员
            $res['review_status'] = 1;
        }
        if ($request['type'] == 2) {
            //经销商
            $res['review_status'] = 2;
        }
        $data->save($res);
        AdminLog::build()->add($userInfo['uuid'], self::menu(), '设置类型');
        return true;
    }

    static public function setNote($request, $userInfo)
    {
        $data = PartnerReview::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
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
            $Partner = Partner::build()->where('uuid', $id)->findOrFail();
            $data = User::build()->getFullTree($Partner->user_uuid);
            return $data;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id,$userInfo)
    {
        try {
            $data = Partner::build()->where('uuid',$id)->findOrFail();
            $data->save(['is_deleted' => 2]);
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '删除');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
