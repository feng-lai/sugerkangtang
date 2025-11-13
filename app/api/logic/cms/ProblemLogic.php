<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\Problem;
use think\Exception;
use think\Db;

/**
 * 常见问题-逻辑
 * User: Yacon
 * Date: 2022-08-11
 * Time: 21:24
 */
class ProblemLogic
{
  static public function cmsList($userInfo)
  {
    $result = Problem::build()
      ->field('*')
      ->order('update_time desc')
      ->select();
    AdminLog::build()->add($userInfo['uuid'], '常见问题管理','查询列表');
    return $result;
  }

  static public function cmsDetail($id,$userInfo)
  {
      $data = Problem::build()
      ->where('uuid', $id)
      ->field('*')
      ->find();
    AdminLog::build()->add($userInfo['uuid'], '常见问题管理','查询详情：'.$data->title);
    return $data;
  }

   static public function cmsAdd($request,$userInfo){
     try {
       $data = [
         'uuid' => uuid(),
         'title'=>$request['title'],
         'content'=>$request['content'],
         'create_time' => now_time(time()),
         'update_time' => now_time(time()),
       ];
       Problem::build()->insert($data);
       AdminLog::build()->add($userInfo['uuid'], '常见问题管理','新增：'.$data['title']);
       return $data['uuid'];
     } catch (Exception $e) {
         throw new Exception($e->getMessage(), 500);
     }
   }

  static public function cmsEdit($request,$userInfo)
  {
    try {
      $data = Problem::build()->where('uuid', $request['uuid'])->findOrFail();
      AdminLog::build()->add($userInfo['uuid'], '常见问题管理','更新：'.$data->title);
      $data->save($request);
      return true;
    } catch (Exception $e) {
      throw new Exception($e->getMessage(), 500);
    }
  }

   static public function cmsDelete($id,$userInfo){
     try {
       $data = Problem::build()->where('uuid',$id)->findOrFail();
       $data->delete();
       AdminLog::build()->add($userInfo['uuid'], '常见问题管理','删除：'.$data->title);
       return true;
     } catch (Exception $e) {
         throw new Exception($e->getMessage(), 500);
     }
   }
}
