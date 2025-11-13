<?php

namespace app\api\logic\mini;

use app\api\model\ContestantImg;
use app\api\model\Contestant;
use think\Exception;
use think\Db;

/**
 * 选手海报素材照片-逻辑
 * User:
 * Date: 2022-07-21
 * Time: 14:31
 */
class ContestantImgLogic
{
  static public function cmsList($request,$userInfo)
  {
    $contestant = Contestant::build()->where('user_uuid',$userInfo['uuid'])->find();
    if(!$contestant){
      throw new Exception('选手数据不存在', 500);
    }
    $map['contestant_uuid'] = ['=',$contestant->uuid];
    $map['type'] =  ['=',$request['type']];
    $result = ContestantImg::build()->where($map)->order('create_time asc')->select();
    return $result;
  }

  static public function miniAdd($request, $userInfo)
  {
    try {
      $contestant = Contestant::build()->where('user_uuid',$userInfo['uuid'])->findOrFail();
      $img = ContestantImg::build();
      $img->uuid = uuid();
      $img->contestant_uuid = $contestant->uuid;
      $img->img = $request['img'];
      $img->type = $request['type'];
      $img->is_extra = 3;
      $img->save();
      return $img->uuid;

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
