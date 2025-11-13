<?php

namespace app\api\logic\mini;

use app\api\model\ContestantStyleVideo;
use app\api\model\ContestantStyle;
use app\api\model\Contestant;
use app\api\model\ContestantStyleVideoAgree;
use think\Exception;
use think\Db;

/**
 * 亚姐值视频-逻辑
 * User:
 * Date:
 * Time:
 */
class ContestantStyleVideoLogic
{
  static public function miniAdd($request, $userInfo)
  {
    try {
      $contestant = Contestant::build()->where('user_uuid',$userInfo['uuid'])->find();
      if(!$contestant){
        return ['msg'=>'只有选手才能上传亚姐值视频'];
      }
      $contestant_style = ContestantStyle::build()->where('state',1)->find($request['contestant_style_uuid']);
      if(!$contestant_style){
        return ['msg'=>'亚姐值不存在或者没开启'];
      }
      $agree = ContestantStyleVideo::build();
      $agree->uuid = uuid();
      $agree->contestant_uuid = $contestant->uuid;
      $agree->img = $request['img'];
      $agree->video = $request['video'];
      $agree->contestant_style_uuid = $request['contestant_style_uuid'];
      $agree->create_time = date("Y-m-d H:i:s", time());
      $agree->update_time = date("Y-m-d H:i:s", time());
      $agree->save();
      return $agree->uuid;

    } catch (Exception $e) {
      throw new Exception($e->getMessage(), 500);
    }
  }

  static public function miniEdit($request, $userInfo)
  {
    try {
      $contestant = Contestant::build()->where('user_uuid',$userInfo['uuid'])->find();
      if(!$contestant){
        return ['msg'=>'只有选手才能上传亚姐值视频'];
      }
      $contestant_style = ContestantStyle::build()->where('state',1)->find($request['contestant_style_uuid']);
      if(!$contestant_style){
        return ['msg'=>'亚姐值不存在或者没开启'];
      }
      $agree = ContestantStyleVideo::build()->find($request['uuid']);
      if(!$agree){
        return ['msg'=>'数据不存在'];
      }
      $agree->contestant_uuid = $contestant->uuid;
      $agree->img = $request['img'];
      $agree->video = $request['video'];
      $agree->state = 1;
      $agree->contestant_style_uuid = $request['contestant_style_uuid'];
      $agree->create_time = date("Y-m-d H:i:s", time());
      $agree->update_time = date("Y-m-d H:i:s", time());
      $agree->save();
      return $agree->uuid;

    } catch (Exception $e) {
      throw new Exception($e->getMessage(), 500);
    }
  }

  static public function miniList($request, $userInfo){
    $map['is_deleted'] = 1;
    $request['contestant_uuid']?$map['contestant_uuid'] = $request['contestant_uuid']:'';
    $request['contestant_style_uuid']?$map['contestant_style_uuid'] = $request['contestant_style_uuid']:'';
    $request['state']?$map['state'] = $request['state']:'';
    $data = ContestantStyleVideo::build()
      ->where($map)
      ->order('gift desc,agree desc,view desc,create_time desc')
      ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
    foreach($data as $v){
      $v->is_agree = ContestantStyleVideoAgree::build()->where(['contestant_style_video_uuid'=>$v->uuid,'user_uuid'=>$userInfo['uuid']])->count();
    }
    $data = objToArray($data);
    $data['score'] = round(ContestantStyleVideo::build()->where($map)->sum('gift')/10000,1);
    return $data;
  }

  static public function miniDetail($id,$userInfo){
    $data = ContestantStyleVideo::build()->where('is_deleted',1)->find($id);
    if(!$data){
      return ['msg'=>'视频已被删除或者无此数据'];
    }
    $data->is_agree = ContestantStyleVideoAgree::build()->where(['contestant_style_video_uuid'=>$data->uuid,'user_uuid'=>$userInfo['uuid']])->count();
    $data->contestant_style_name = ContestantStyle::build()->where('uuid',$data->contestant_style_uuid)->value('name');
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

   static public function miniDelete($id, $userInfo)
   {
     try {
       Db::startTrans();
       $contestant = Contestant::build()->where('user_uuid',$userInfo['uuid'])->find();
       if(!$contestant){
         return ['msg'=>'只有选手才能删除亚姐值视频'];
       }
       $data = ContestantStyleVideo::build()->where(['contestant_uuid'=>$contestant->uuid,'uuid'=>$id])->find();
       if(!$data){
         return ['msg'=>'只有本人才能删除亚姐值视频'];
       }
       $data['is_deleted'] = 2;
       $data->save();
       Db::commit();
       return true;
     } catch (Exception $e) {
       Db::rollback();
       throw new Exception($e->getMessage(), 500);
     }
   }
}
