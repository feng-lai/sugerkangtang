<?php

namespace app\api\logic\mini;

use app\api\model\Feedback;
use think\Exception;
use think\Db;

/**
 * 意见反馈-逻辑
 * User: Yacon
 * Date: 2022-07-21
 * Time: 14:31
 */
class FeedbackLogic
{
  static public function miniList($userInfo)
  {
    //七天签到情况
    return Sign::build()->week_info($userInfo['uuid']);
  }
  static public function miniAdd($request)
  {
    try {
      Db::startTrans();
      $sign = Feedback::build();
      $sign->uuid = uuid();
      $sign->user_uuid = $request['user_uuid'];
      $sign->content = $request['content'];
      $sign->create_time = date("Y-m-d H:i:s", time());
      $sign->update_time = date("Y-m-d H:i:s", time());
      $sign->save();
      Db::commit();
      return true;

    } catch (Exception $e) {
      Db::rollback();
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
