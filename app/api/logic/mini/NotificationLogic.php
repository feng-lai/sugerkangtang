<?php

namespace app\api\logic\mini;

use app\api\model\Notification;
use think\Exception;
use think\Db;
use app\api\model\Contestant;

/**
 * 动态-逻辑
 * User:
 * Date: 2022-08-11
 * Time: 21:24
 */
class NotificationLogic
{
  static public function cmsList($request,$userInfo)
  {
    $map['state'] = 1;
    $map['is_deleted'] = 1;
    if($userInfo){
      $contestant = Contestant::build()->where('user_uuid',$userInfo['uuid'])->find();
      if($contestant){
        $map['province'] = ['=',$contestant->province];
        $map['city'] = ['=',$contestant->city];
        $map['area'] = ['=',$contestant->area];
      }
      $result = Notification::build()
        ->where($map)
        ->whereOrRaw('state = 1 and is_deleted = 1 and province is null')
        ->whereOrRaw('state = 1 and is_deleted = 1 and province = ""')
        ->order('create_time desc')
        ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
    }else{
      $map['province'] = '';
      $result = Notification::build()
        ->where($map)
        ->whereOrRaw('state = 1 and is_deleted = 1 and province is null')
        ->order('create_time desc')
        ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
    }

    foreach ($result as $v){
      $v->content = str_replace('&nbsp;','',strip_tags($v->content));
    }
    return $result;
  }

  static public function cmsDetail($id)
  {
    return  Notification::build()
      ->where('uuid', $id)
      ->field('*')
      ->find();
  }

   static public function cmsAdd($request){
     try {
       $data = [
         'uuid' => uuid(),
         'content'=>$request['content'],
         'create_time' => now_time(time()),
         'update_time' => now_time(time()),
       ];
       Config::build()->insert($data);
       return $data['uuid'];
     } catch (Exception $e) {
         throw new Exception($e->getMessage(), 500);
     }
   }

  static public function cmsEdit($request)
  {
    try {
      $user = Config::build()->where('key', $request['key'])->find();
      $user->save(['value'=>$request['value']]);
      return true;
    } catch (Exception $e) {
      throw new Exception($e->getMessage(), 500);
    }
  }

   static public function cmsDelete($id){
     try {
       $user = Config::build()->where('uuid', $id)->find();
       $user->save(['delete'=>1]);
       return true;
     } catch (Exception $e) {
         throw new Exception($e->getMessage(), 500);
     }
   }
}
