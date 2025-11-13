<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\Notification;
use think\Exception;
use think\Db;

/**
 * 公告-逻辑
 * User: Yacon
 * Date: 2023-03-20
 * Time: 00:25
 */
class NotificationLogic
{
  static public function cmsPage($request, $userInfo)
  {
    $map['a.is_deleted'] = 1;
    $request['title'] ?  $map['a.title'] = ['like', "%{$request['title']}%"] : '';
    $request['province_uuid'] ? $map['a.province_uuid'] = ['=',$request['province_uuid']]: '';
    $request['city_uuid'] ? $map['a.city_uuid'] = ['=',$request['city_uuid']]: '';
    $request['area_uuid'] ? $map['a.area_uuid'] = ['=',$request['area_uuid']]: '';
    $request['state'] ? $map['a.state'] = ['=',$request['state']]: '';
    if($request['state'] && $request['state'] == 3){
      unset($map['a.state']);
      $map['a.is_deleted'] = 2;
    }
    $result = Notification::build()
      ->field('*')
      ->alias('a')
      ->where($map)
      ->order('a.create_time desc')
      ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
    AdminLog::build()->add($userInfo['uuid'], '动态管理','查询列表');
    return $result;
  }

  static public function cmsList($request, $userInfo)
  {
    $map['a.is_deleted'] = 1;
    $result = Notification::build()
      ->field('*')
      ->alias('a')
      ->where($map)
      ->order('a.create_time desc')
      ->select();
    AdminLog::build()->add($userInfo['uuid'], '动态管理','查询列表');
    return $result;
  }

  static public function cmsDetail($id, $userInfo)
  {
    $result = Notification::build()
      ->field('*')
      ->alias('a')
      ->where('a.uuid', $id)
      ->find();
    AdminLog::build()->add($userInfo['uuid'], '动态管理','查询详情：'.$result->title);
    return $result;
  }

  static public function cmsAdd($request, $userInfo)
  {
    try {
      Db::startTrans();
      if (!$request['title']) throw new Exception('请填写标题');
      $notification = Notification::build();
      $notification['uuid'] = uuid();
      $notification['create_time'] = now_time(time());
      $notification['update_time'] = now_time(time());
      $notification['title'] = $request['title'];
      $notification['content'] = $request['content'];
      $notification['province'] = $request['province'];
      $notification['city'] = $request['city'];
      $notification['img'] = $request['img'];
      $notification['area'] = $request['area'];
      $notification['province_uuid'] = $request['province_uuid'];
      $notification['city_uuid'] = $request['city_uuid'];
      $notification['area_uuid'] = $request['area_uuid'];
      $notification['state'] = $request['state'];
      $notification->save();
      AdminLog::build()->add($userInfo['uuid'], '动态管理','新增：'.$notification['title']);
      Db::commit();
      return $notification['uuid'];
    } catch (Exception $e) {
      Db::rollback();
      throw new Exception($e->getMessage(), 500);
    }
  }

  static public function cmsEdit($request, $userInfo)
  {
    try {
      Db::startTrans();
      $notification = Notification::build()->where(['uuid' => $request['uuid']])->findOrFail();
      $request['update_time'] = now_time(time());
      $notification->save($request);
      AdminLog::build()->add($userInfo['uuid'], '动态管理','更新：'.$notification['title']);
      Db::commit();
      return true;
    } catch (Exception $e) {
      Db::rollback();
      throw new Exception($e->getMessage(), 500);
    }
  }

  static public function cmsDelete($id, $userInfo)
  {
    try {
      Db::startTrans();
      $notification = Notification::build()->where(['uuid' => $id])->find();
      $notification['update_time'] = now_time(time());
      $notification['is_deleted'] = 2;
      $notification->save();
      AdminLog::build()->add($userInfo['uuid'], '动态管理','删除：'.$notification->title);
      Db::commit();
      return true;
    } catch (Exception $e) {
      Db::rollback();
      throw new Exception($e->getMessage(), 500);
    }
  }
}
