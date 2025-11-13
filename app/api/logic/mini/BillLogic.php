<?php

namespace app\api\logic\mini;

use app\api\model\Bill;
use think\Exception;
use think\Db;

/**
 * 账单-逻辑
 * User: Yacon
 * Date: 2022-07-21
 * Time: 14:31
 */
class BillLogic
{
    static public function cmsList($request, $userinfo)
    {
        $where = [
            'is_deleted'=>1,
            'site_id'=>$request['site_id'],
            'user_uuid'=>$userinfo['uuid'],
            'type'=>['in',[3,6,2]] //类别  3=提现申请  6=提现退回 2=佣金结算
        ];
        if($request['status']){
            if($request['status'] == 1){
                $where['type'] = ['in',[6,2]];
            }else{
                $where['type'] = 3;
            }
        }
        if($request['start_time'] && $request['end_time']){
            $where['create_time'] = ['between time', [$request['start_time'], $request['end_time']]];
        }
        $result = Bill::build()
            ->where($where)
            ->order('create_time desc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
        return $result;
    }

    static public function miniAdd($request, $userInfo)
    {
        try {
            Contestant::build()->where('uuid', $request['contestant_uuid'])->findOrFail();
            if ($request['type'] == 1 && !Attention::build()->where(['contestant_uuid' => $request['contestant_uuid'], 'user_uuid' => $userInfo['uuid']])->count()) {
                $agree = Attention::build();
                $agree->uuid = uuid();
                $agree->contestant_uuid = $request['contestant_uuid'];
                $agree->user_uuid = $userInfo['uuid'];
                $agree->create_time = date("Y-m-d H:i:s", time());
                $agree->update_time = date("Y-m-d H:i:s", time());
                $agree->save();
            }
            if ($request['type'] == -1) {
                Agree::build()->where(['contestant_uuid' => $request['contestant_uuid'], 'user_uuid' => $userInfo['uuid']])->delete();
            }
            return true;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    // static public function miniEdit($request, $userInfo)
    // {
    //   try {
    //     Db::startTrans();
    //     $user = User::build()->where('uuid', $request['uuid'])->find();
    //     $user['update_time'] = now_time(time());
    //     $user->save();
    //     Db::commit();
    //     return true;
    //   } catch (Exception $e) {
    //     Db::rollback();
    //     throw new Exception($e->getMessage(), 500);
    //   }
    // }

    // static public function miniDelete($id, $userInfo)
    // {
    //   try {
    //     Db::startTrans();
    //     User::build()->where('uuid', $id)->update(['is_deleted' => 2]);
    //     Db::commit();
    //     return true;
    //   } catch (Exception $e) {
    //     Db::rollback();
    //     throw new Exception($e->getMessage(), 500);
    //   }
    // }
}
