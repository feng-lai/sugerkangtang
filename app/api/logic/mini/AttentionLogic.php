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
use app\api\model\UserInterrest;
use app\api\model\UserToken;
use think\Exception;
use think\Db;

/**
 * 关注-逻辑
 * User: Yacon
 * Date: 2022-07-21
 * Time: 14:31
 */
class AttentionLogic
{
  static public function cmsList($request,$userinfo)
  {
    $result = Attention::build()
      ->field('a.uuid,u.nickname,c.half_photo,c.city,a.contestant_uuid,c.gift,u.avatar,c.id_photo,c.name,a.create_time')
      ->alias('a')
      ->join('contestant c','c.uuid = a.contestant_uuid','LEFT')
      ->join('user u','u.uuid = c.user_uuid','LEFT')
      ->where('a.user_uuid',$userinfo['uuid'])
      ->where('a.type',3)
      ->order('c.gift desc,a.create_time desc')
      ->group('a.contestant_uuid')
      ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
    foreach($result as $v){
      $v->agree = Agree::build()->where('contestant_uuid',$v->contestant_uuid)->count();
    }
    return $result;
  }

  static public function miniAdd($request, $userInfo)
  {
    try {
      Contestant::build()->where('uuid',$request['contestant_uuid'])->where('state','in','2,4')->findOrFail();
      if($request['type'] == 1 && !Attention::build()->where(['contestant_uuid'=>$request['contestant_uuid'],'user_uuid'=>$userInfo['uuid']])->where('type','<>',1)->count()){
        //粉丝团
        $agree = Attention::build();
        $agree->uuid = uuid();
        $agree->contestant_uuid = $request['contestant_uuid'];
        $agree->user_uuid = $userInfo['uuid'];
        $agree->type=3;
        $agree->create_time = date("Y-m-d H:i:s", time());
        $agree->update_time = date("Y-m-d H:i:s", time());
        $agree->save();
        //是否是选手
        $is = Contestant::build()->where('user_uuid',$userInfo['uuid'])->where('state','in','2,4')->find();
        if($is){
          //后援团
          $agrees = Attention::build();
          $agrees->uuid = uuid();
          $agrees->contestant_uuid = $request['contestant_uuid'];
          $agrees->user_uuid = $userInfo['uuid'];
          $agrees->type=2;
          $agrees->create_time = date("Y-m-d H:i:s", time());
          $agrees->update_time = date("Y-m-d H:i:s", time());
          $agrees->save();
        }

      }
      if($request['type'] == 2){
        Attention::build()->where(['contestant_uuid'=>$request['contestant_uuid'],'user_uuid'=>$userInfo['uuid']])->where('type','in','2,3')->delete();
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
