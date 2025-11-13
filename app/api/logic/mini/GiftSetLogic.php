<?php

namespace app\api\logic\mini;

use app\api\model\GiftSet;
use think\Exception;
use think\Db;

/**
 * 礼物-逻辑
 * User: Yacon
 * Date: 2022-07-21
 * Time: 14:31
 */
class GiftSetLogic
{

  static public function cmsList()
  {
    return GiftSet::build()->select();
  }
  static public function miniAdd($request, $userInfo)
  {
    try {
      Contestant::build()->where('uuid',$request['contestant_uuid'])->findOrFail();
      if($request['type'] == 1 && !Agree::build()->where(['contestant_uuid'=>$request['contestant_uuid'],'user_uuid'=>$userInfo['uuid']])->count()){
        $agree = Agree::build();
        $agree->uuid = uuid();
        $agree->contestant_uuid = $request['contestant_uuid'];
        $agree->user_uuid = $userInfo['uuid'];
        $agree->create_time = date("Y-m-d H:i:s", time());
        $agree->update_time = date("Y-m-d H:i:s", time());
        $agree->save();
      }
      if($request['type'] == -1){
        Agree::build()->where(['contestant_uuid'=>$request['contestant_uuid'],'user_uuid'=>$userInfo['uuid']])->delete();
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
