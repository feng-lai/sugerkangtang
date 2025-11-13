<?php

namespace app\api\logic\mini;

use app\api\model\Contestant;
use app\api\model\Matchs;
use Exception;
use think\Db;

/**
 * 用户报名信息-逻辑
 * User: Yacon
 * Date: 2023-03-20
 * Time: 22:35
 */
class UserMatchsLogic
{
  static public function miniList($request, $userInfo)
  {
    $map['a.is_deleted'] = 1;

    // 查询上架的赛事
    $matchsUuid = Matchs::build()->where(['state' => 1, 'is_deleted' => 1])->value('uuid');
    if (!$matchsUuid) return null;

    // 查询当前用户的预约情况
    $result = Contestant::build()->where(['is_deleted' => 1, 'user_uuid' => $userInfo['uuid'], 'matchs_uuid' => $matchsUuid])->find();

    return $result;
  }

  // static public function miniDetail($id,$userInfo){
  //     $result=UserMatchs::build()
  //       ->field('*')
  //       ->alias('a')
  //       ->where('a.uuid',$id)
  //       ->find();
  //     return $result;
  // }

  static public function miniAdd($request, $userInfo)
  {
    try {
      Db::startTrans();

      if (!$request['contestant_uuid']) throw new Exception('请提供报名信息');

      $contestant = Contestant::build()->where(['uuid' => $request['contestant_uuid']])->find();

      if($contestant['state'] != 2) throw new Exception("当前报名信息未审核，请等候通知再签署合同");

      $contestant['update_time'] = now_time(time());
      $contestant['state'] = 4;
      $contestant->save();
      Db::commit();
      return true;
    } catch (Exception $e) {
      Db::rollback();
      throw new Exception($e->getMessage(), 500);
    }
  }

  // static public function miniEdit($request,$userInfo){
  //   try {
  //     Db::startTrans();
  //     $userMatchs = UserMatchs::build()->where(['uuid' => $request['uuid']])->find();
  //     $userMatchs['update_time'] = now_time(time());
  //     $userMatchs->save();
  //     Db::commit();
  //     return true;
  //   } catch (Exception $e) {
  //       Db::rollback();
  //       throw new Exception($e->getMessage(), 500);
  //   }
  // }

  // static public function miniDelete($id,$userInfo){
  //   try {
  //     Db::startTrans();
  //     $userMatchs = UserMatchs::build()->where(['uuid'=>$id])->find();
  //     $userMatchs['update_time'] = now_time(time());
  //     $userMatchs['is_deleted'] = 2;
  //     $userMatchs->save();
  //     Db::commit();
  //     return true;
  //   } catch (Exception $e) {
  //       Db::rollback();
  //       throw new Exception($e->getMessage(), 500);
  //   }
  // }
}
