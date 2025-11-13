<?php

namespace app\api\logic\mini;

use app\api\model\MatchsCity;
use app\api\model\MatchsPoint;
use app\api\model\Region;

/**
 * 有赛事的省市区树-逻辑
 * User: Yacon
 * Date: 2023-03-27
 * Time: 21:10
 */
class MatchsAreaTreeLogic
{
  // static public function miniPage($request,$userInfo){
  //   $map['a.is_deleted'] = 1;
  //   $result=MatchsAreaTree::build()
  //       ->field('*')
  //       ->alias('a')
  //       ->where($map)
  //       ->order('a.create_time desc')
  //       ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
  //   return $result;
  // }

  static public function miniList($request, $userInfo)
  {
    $map= [];
    if ($request['state']) $map['b.state'] = $request['state'];
    $citys = MatchsCity::build()
      ->field('a.*')
      ->alias('a')
      ->join('matchs b', 'b.uuid = a.matchs_uuid')
      ->where($map)
      ->select();
    $citys = objToArray($citys);
    $province_uuids = array_column($citys, 'province_uuid');
    $city_uuid = array_column($citys, 'city_uuid');

    $points = MatchsPoint::build()
      ->field('a.*')
      ->alias('a')
      ->join('matchs b', 'b.uuid = a.matchs_uuid')
      ->where($map)
      ->column('area_uuid');

    $area_ids = array_merge($province_uuids, $city_uuid, $points);

    $areas  = Region::build()->field('*')->where(['area_id' => ['in', $area_ids]])->select();
    $areas = objToArray($areas);

    $areas = array_map(function ($item) {
      $item['id'] = $item['area_id'];
      $item['pid'] = $item['parent_id'];
      return $item;
    }, $areas);

    $areas = getTreeList($areas, -1);

    return $areas;
  }

  // static public function miniDetail($id,$userInfo){
  //     $result=MatchsAreaTree::build()
  //       ->field('*')
  //       ->alias('a')
  //       ->where('a.uuid',$id)
  //       ->find();
  //     return $result;
  // }

  // static public function miniAdd($request,$userInfo){
  //   try {
  //     Db::startTrans();
  //     $matchsAreaTree = MatchsAreaTree::build();
  //     $matchsAreaTree['uuid'] = uuid();
  //     $matchsAreaTree['create_time'] = now_time(time());
  //     $matchsAreaTree['update_time'] = now_time(time());
  //     $matchsAreaTree->save();
  //     Db::commit();
  //     return $matchsAreaTree['uuid'];
  //   } catch (Exception $e) {
  //       Db::rollback();
  //       throw new Exception($e->getMessage(), 500);
  //   }
  // }

  // static public function miniEdit($request,$userInfo){
  //   try {
  //     Db::startTrans();
  //     $matchsAreaTree = MatchsAreaTree::build()->where(['uuid' => $request['uuid']])->find();
  //     $matchsAreaTree['update_time'] = now_time(time());
  //     $matchsAreaTree->save();
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
  //     $matchsAreaTree = MatchsAreaTree::build()->where(['uuid'=>$id])->find();
  //     $matchsAreaTree['update_time'] = now_time(time());
  //     $matchsAreaTree['is_deleted'] = 2;
  //     $matchsAreaTree->save();
  //     Db::commit();
  //     return true;
  //   } catch (Exception $e) {
  //       Db::rollback();
  //       throw new Exception($e->getMessage(), 500);
  //   }
  // }
}
