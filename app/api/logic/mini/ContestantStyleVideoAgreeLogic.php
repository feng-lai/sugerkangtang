<?php

namespace app\api\logic\mini;

use app\api\model\ContestantStyleVideoAgree;
use app\api\model\ContestantStyleVideo;
use app\api\model\Contestant;
use think\Exception;
use think\Db;

/**
 * 亚姐值视频点赞-逻辑
 * User:
 * Date:
 * Time:
 */
class ContestantStyleVideoAgreeLogic
{
  static public function miniAdd($request, $userInfo)
  {
    try {

      if(!$request['contestant_style_video_uuid']){
        return ['msg'=>'亚姐值视频不能为空'];
      }
      if(!$request['matchs_step_uuid']){
        //return ['msg'=>'赛段不能为空'];
      }
      if(ContestantStyleVideoAgree::build()->where(['contestant_style_video_uuid'=>$request['contestant_style_video_uuid'],'user_uuid'=>$userInfo['uuid']])->count()){
        return true;
      }
      Db::startTrans();
      $agree = ContestantStyleVideoAgree::build();
      $agree->uuid = uuid();
      $agree->matchs_step_uuid = $request['matchs_step_uuid'];
      $agree->contestant_style_video_uuid = $request['contestant_style_video_uuid'];
      $agree->user_uuid = $userInfo['uuid'];
      $agree->create_time = date("Y-m-d H:i:s", time());
      $agree->update_time = date("Y-m-d H:i:s", time());
      $agree->save();
      ContestantStyleVideo::build()->where('uuid',$request['contestant_style_video_uuid'])->setInc('agree');
      $contestant_uuid = ContestantStyleVideo::build()->where('uuid',$request['contestant_style_video_uuid'])->value('contestant_uuid');
      Contestant::build()->where('uuid',$contestant_uuid)->setInc('agree');
      Db::commit();
      return true;
    } catch (Exception $e) {
      Db::rollback();
      throw new Exception($e->getMessage(), 500);
    }
  }

   static public function miniDelete($id, $userInfo)
   {
     try {
       Db::startTrans();
       $res = ContestantStyleVideoAgree::build()->where(['contestant_style_video_uuid'=>$id,'user_uuid'=>$userInfo['uuid']])->delete();
       if($res){
         ContestantStyleVideo::build()->where('uuid',$id)->setDec('agree');
         $contestant_uuid = ContestantStyleVideo::build()->where('uuid',$id)->value('contestant_uuid');
         Contestant::build()->where('uuid',$contestant_uuid)->setDec('agree');
       }
       Db::commit();
       return true;
     } catch (Exception $e) {
       Db::rollback();
       throw new Exception($e->getMessage(), 500);
     }
   }
}
