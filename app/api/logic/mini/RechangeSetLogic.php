<?php

namespace app\api\logic\mini;

use app\api\model\RechangeSet;
use app\api\model\Config;
use think\Exception;
use think\Db;

/**
 * 充值-逻辑
 * User: Yacon
 * Date: 2022-07-21
 * Time: 14:31
 */
class RechangeSetLogic
{

  static public function cmsList()
  {
    $data = RechangeSet::build()->select();
    $persent = Config::build()->where('key','COINS_PRICE')->value('value');
    foreach ($data as $v){
      $v->price = round($v->coins*$persent,2);
    }
    return $data;
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
