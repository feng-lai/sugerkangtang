<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\Feedback;
use think\Exception;
use think\Db;

/**
 * 常见问题-逻辑
 * User: Yacon
 * Date: 2022-08-11
 * Time: 21:24
 */
class FeedbackLogic
{
  static public function cmsList($request,$userInfo)
  {
    $map['a.delete'] = $request['delete'] ? $request['delete']:0;
    $request['content'] ? $map['a.content'] = ['like','%'.$request['content'].'%'] : '';
    $result = Feedback::build()
      ->alias('a')
      ->join('user u','a.user_uuid = u.uuid','left')
      ->field('u.nickname,a.*')
      ->where($map)
      ->order('a.update_time desc')
      ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
    AdminLog::build()->add($userInfo['uuid'], '意见反馈管理','查询列表');
    return $result;
  }

  static public function cmsDetail($id,$userInfo)
  {
    $data = Feedback::build()
      ->where('uuid', $id)
      ->field('*')
      ->findOrFail();
    AdminLog::build()->add($userInfo['uuid'], '意见反馈管理','查询详情:'.$data->content);
    return $data;
  }

   static public function cmsAdd($request){
     try {
       $data = [
         'uuid' => uuid(),
         'user_uuid'=>$request['user_uuid'],
         'content'=>$request['content'],
         'create_time' => now_time(time()),
         'update_time' => now_time(time()),
       ];
       Feedback::build()->insert($data);
       return $data['uuid'];
     } catch (Exception $e) {
         throw new Exception($e->getMessage(), 500);
     }
   }

  static public function cmsEdit($request)
  {
    try {
      $user = Feedback::build()->where('uuid', $request['uuid'])->find();
      $user->save($request);
      return true;
    } catch (Exception $e) {
      throw new Exception($e->getMessage(), 500);
    }
  }

   static public function cmsDelete($id,$userInfo){
     try {
       $data = Feedback::build()->where('uuid', $id)->findOrFail();
       $data->save(['delete'=>1]);
       AdminLog::build()->add($userInfo['uuid'], '意见反馈管理','删除：'.$data->content);
       return true;
     } catch (Exception $e) {
         throw new Exception($e->getMessage(), 500);
     }
   }
}
