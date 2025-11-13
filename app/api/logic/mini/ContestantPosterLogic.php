<?php

namespace app\api\logic\mini;

use app\api\model\ContestantPoster;
use app\api\model\ContestantImg;
use app\api\model\Contestant;
use think\Exception;
use think\Db;

/**
 * 选手海报-逻辑
 * User:
 * Date: 2022-07-21
 * Time: 14:31
 */
class ContestantPosterLogic
{
  static public function cmsList($request)
  {
    $contestant = Contestant::build()->where('uuid',$request['contestant_uuid'])->findOrFail();
    $map['contestant_uuid'] = ['=',$contestant->uuid];
    $map['type'] =  ['=',$request['type']];
    $request['contestant_img_uuid']?$map['contestant_img_uuid'] = ['=',$request['contestant_img_uuid']]:'';
    $request['matchs_step_uuid']?$map['matchs_step_uuid'] = ['=',$request['matchs_step_uuid']]:'';
    $request['is_extra']?$map['is_extra'] = ['=',$request['is_extra']]:'';
    $request['template_name']?$map['template_name'] = ['=',$request['template_name']]:'';
    $result = ContestantPoster::build()->where($map)->order('create_time asc')->select();
    return $result;
  }

  static public function miniAdd($request, $userInfo)
  {
    try {
      Db::startTrans();
      //判断生成海报次数
      $contestant = Contestant::build()->where('user_uuid',$userInfo['uuid'])->find();
      if(!$contestant){
        throw new Exception('选手不存在', 500);
      }
      $contestant_img = ContestantImg::build()->where('uuid',$request['contestant_img_uuid'])->where('type',$request['type'])->find();
      if(!$contestant_img){
        throw new Exception('海报素材不存在', 500);
      }
      if($request['type'] == 1 && $contestant_img->is_extra == 3){
        //参赛海报需要计算收费次数
        $map['is_extra'] =  3;
        $map['type'] =  1;
        $map['contestant_uuid'] = $contestant->uuid;
        $result = ContestantPoster::build()->where($map)->order('create_time desc')->count();
        if($result >= 5){
          throw new Exception('免费次数已用完', 500);
        }
      }

      $img = ContestantPoster::build();
      $img->uuid = uuid();
      $img->contestant_uuid = $contestant->uuid;
      $img->is_extra = $contestant_img->is_extra;
      $img->contestant_img_uuid = $request['contestant_img_uuid'];
      $img->img = $request['img'];
      $img->template_name = $request['template_name'];
      $img->type = $request['type'];
      $img->save();
      //素材使用+1
      ContestantImg::build()->where('uuid',$request['contestant_img_uuid'])->setInc('use');
      Db::commit();
      return $img->uuid;
    } catch (Exception $e) {
      Db::rollback();
      throw new Exception($e->getMessage(), 500);
    }
  }

   static public function miniEdit($request, $userInfo)
   {
     try {
       Db::startTrans();
       $user = ContestantPoster::build()->where('uuid', $request['uuid'])->find();
       $user['status'] = $request['status'];
       $user->save();
       Db::commit();
       return true;
     } catch (Exception $e) {
       Db::rollback();
       throw new Exception($e->getMessage(), 500);
     }
   }

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
