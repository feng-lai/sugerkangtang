<?php

namespace app\api\logic\mini;

use app\api\model\AdminLog;
use app\api\model\User;
use app\api\model\UserRelation;
use think\Exception;
use think\Db;

/**
 * 用户分享关系-逻辑
 * User: Yacon
 * Date: 2023-03-20
 * Time: 16:29
 */
class UserRelationLogic
{
  static public function miniPage($request, $userInfo)
  {
    $map['a.is_deleted'] = 1;
    array_key_exists('openid', $userInfo) ? $map['a.user_uuid'] = $userInfo['uuid'] : '';
    $request['user_uuid'] ? $map['a.user_uuid'] = $request['user_uuid'] : '';
    $result = UserRelation::build()
      ->field('*')
      ->alias('a')
      ->where($map)
      ->order('a.create_time desc')
      ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
    $result = objToArray($result);
    $result['data'] = array_map(function ($item) {
      $item['new_user_mobile'] = User::build()->where(['uuid' => $item['new_user_uuid']])->value('mobile');
      return $item;
    }, $result['data']);
    AdminLog::build()->add($userInfo['uuid'], '用户分享信息管理','查询列表');
    return $result;
  }

  // static public function miniList($request, $userInfo)
  // {
  //   $map['a.is_deleted'] = 1;
  //   $result = UserRelation::build()
  //     ->field('*')
  //     ->alias('a')
  //     ->where($map)
  //     ->order('a.create_time desc')
  //     ->select();
  //   return $result;
  // }

  // static public function miniDetail($id,$userInfo){
  //     $result=UserRelation::build()
  //       ->field('*')
  //       ->alias('a')
  //       ->where('a.uuid',$id)
  //       ->find();
  //     return $result;
  // }

  static public function miniAdd($request, $userInfo)
  {
    try {
      Db::startTrans();
      if (!$request['user_uuid']) throw new Exception("分享用户不能为空");
      if ($request['user_uuid'] == $userInfo['uuid']) throw new Exception("不能分享给自己");
      if (UserRelation::build()->where('new_user_uuid' , $request['user_uuid'])->count()) {
        return true;
      }
      if (UserRelation::build()->where(['user_uuid' => $request['user_uuid'], 'new_user_uuid' => $userInfo['uuid']])->count()) {
        return true;
      }

      $userRelation = UserRelation::build();
      $userRelation['uuid'] = uuid();
      $userRelation['create_time'] = now_time(time());
      $userRelation['update_time'] = now_time(time());
      $userRelation['user_uuid'] = $request['user_uuid'];
      $userRelation['new_user_uuid'] = $userInfo['uuid'];
      $userRelation->save();
      Db::commit();
      return $userRelation['uuid'];
    } catch (Exception $e) {
      Db::rollback();
      throw new Exception($e->getMessage(), 500);
    }
  }

  // static public function miniEdit($request,$userInfo){
  //   try {
  //     Db::startTrans();
  //     $userRelation = UserRelation::build()->where(['uuid' => $request['uuid']])->find();
  //     $userRelation['update_time'] = now_time(time());
  //     $userRelation->save();
  //     Db::commit();
  //     return true;
  //   } catch (Exception $e) {
  //       Db::rollback();
  //       throw new Exception($e->getMessage(), 500);
  //   }
  // }

  // static public function miniDelete($id,$userInfo){
  //   try {
  //     Db::startTrans();
  //     $userRelation = UserRelation::build()->where(['uuid'=>$id])->find();
  //     $userRelation['update_time'] = now_time(time());
  //     $userRelation['is_deleted'] = 2;
  //     $userRelation->save();
  //     Db::commit();
  //     return true;
  //   } catch (Exception $e) {
  //       Db::rollback();
  //       throw new Exception($e->getMessage(), 500);
  //   }
  // }
}
