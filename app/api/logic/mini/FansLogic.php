<?php

namespace app\api\logic\mini;

use app\api\model\Captcha;
use app\api\model\Interest;
use app\api\model\InterestBirthday;
use app\api\model\Level;
use app\api\model\Message;
use app\api\model\OrderSetting;
use app\api\model\Contestant;
use app\api\model\Attention;
use app\api\model\Agree;
use app\api\model\ContestantGift;
use app\api\model\UserInterrest;
use app\api\model\UserToken;
use think\Exception;
use think\Db;

/**
 * 粉丝-逻辑
 * User: Yacon
 * Date: 2022-07-21
 * Time: 14:31
 */
class FansLogic
{
  static public function cmsList($request)
  {

    $contestant = Contestant::build()->where('uuid',$request['contestant_uuid'])->findOrFail();
    $map['a.contestant_uuid'] = ['=',$contestant->uuid];
    $request['type']?$map['a.type'] = ['=',$request['type']]:'';
    $result = Attention::build()
      ->field('u.nickname,u.avatar,c.uuid as contestant_uuid,a.type')
      ->alias('a')
      ->join('user u','u.uuid = a.user_uuid','LEFT')
      ->join('contestant c','c.user_uuid = u.uuid','LEFT')
      ->where($map)
      ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
    foreach($result as $v){
      $v->is_contestant = Contestant::build()->where('uuid',$v->contestant_uuid)->where('state','in','2,4')->count();
    }
    return $result;
  }

  static public function miniAdd($request, $userInfo)
  {
    try {
      Contestant::build()->where('uuid',$request['contestant_uuid'])->findOrFail();
      if($request['type'] == 1 && !Attention::build()->where(['contestant_uuid'=>$request['contestant_uuid'],'user_uuid'=>$userInfo['uuid']])->count()){
        $agree = Attention::build();
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
