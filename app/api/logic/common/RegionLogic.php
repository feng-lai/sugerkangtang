<?php

namespace app\api\logic\common;

use app\api\model\Region;

/**
 * 地区-逻辑
 * User: Yacon
 * Date: 2022-02-16
 * Time: 22:54
 */
class RegionLogic
{
  static public function miniList($request)
  {
    $map = [];
    if (!$request['tree']) {
      $map['parent_id'] = $request['parent_id'];
    }
    $result = Region::build()
      ->field('area_id id,area_name name,parent_id pid')
      ->where($map)
      ->order(['sort desc','area_id asc'])
      ->select();

    if ($request['tree']) {
      $result = objToArray($result);
      $result = getTreeList($result, -1);
    }


    return $result;
  }
}
