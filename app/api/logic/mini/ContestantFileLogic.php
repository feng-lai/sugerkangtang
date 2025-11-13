<?php

namespace app\api\logic\mini;

use app\api\model\ContestantFile;
use app\api\model\Contestant;
use think\Exception;
use think\Db;

/**
 * 参赛瞬间-逻辑
 * User:
 * Date:
 * Time:
 */
class ContestantFileLogic
{
  static public function miniAdd($request, $userInfo)
  {
    try {
      $contestant = Contestant::build()->where('user_uuid',$userInfo['uuid'])->find();
      if(!$contestant){
        return ['msg'=>'只有选手才能上传参赛瞬间材料'];
      }
      $agree = ContestantFile::build();
      $agree->uuid = uuid();
      $agree->contestant_uuid = $contestant->uuid;
      $agree->img = $request['img'];
      if($request['type'] == 2) $agree->video = $request['video'];
      $agree->type = $request['type'];
      $agree->title = $request['title'];
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
        return ['msg'=>'只有选手才能上传参赛瞬间材料'];
      }
      $agree = ContestantFile::build()->find($request['uuid']);
      if(!$agree){
        return ['msg'=>'数据不存在'];
      }
      $agree->contestant_uuid = $contestant->uuid;
      $agree->img = $request['img'];
      if($request['type'] == 2) $agree->video = $request['video'];
      $agree->type = $request['type'];
      $agree->title = $request['title'];
      $agree->state = 1;
      $agree->create_time = date("Y-m-d H:i:s", time());
      $agree->update_time = date("Y-m-d H:i:s", time());
      $agree->save();
      return $agree->uuid;

    } catch (Exception $e) {
      throw new Exception($e->getMessage(), 500);
    }
  }


  static public function miniList($request){
    if($request['contestant_uuid']){
      $contestant = Contestant::build()->where('uuid',$request['contestant_uuid'])->find();
      if(!$contestant){
        return ['msg'=>'选手不存在'];
      }
    }

    $map['is_deleted'] = 1;
    $request['contestant_uuid']?$map['contestant_uuid'] = $request['contestant_uuid']:'';
    $request['type']?$map['type'] = $request['type']:'';
    $request['state']?$map['state'] = $request['state']:'';
    $data = ContestantFile::build()->where($map)->order('create_time desc')->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
    return $data;
  }

  static public function miniDetail($id,$userInfo){
    return ContestantFile::build()->where('is_deleted',1)->find($id);
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
         return ['msg'=>'只有选手才能删除参赛瞬间材料'];
       }
       $data = ContestantFile::build()->where(['contestant_uuid'=>$contestant->uuid,'uuid'=>$id])->find();
       if(!$data){
         return ['msg'=>'只有选手本人才能删除'];
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
