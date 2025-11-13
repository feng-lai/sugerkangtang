<?php

namespace app\api\logic\common;

use think\Exception;
use think\Db;
use think\Config;
use app\api\model\MarketUser;

/**
 * 小码短链接-逻辑
 * User:
 * Date:
 * Time: 10:36
 */
class LinkUrlLogic
{
  static public function commonAdd($request)
  {
    try {
      $user = MarketUser::build()->find($request['uuid']);
      if(!$user){
        throw new Exception('用户不存在', 500);
      }
      $arr = [
        'apikey'=>'8ce4e17630149b7eaca259585c0abe51',
        'origin_url'=>$request['origin_url']
      ];
      $result = postData('https://api.xiaomark.com/v1/link/create',json_encode($arr),['Content-Type:application/json']);
      $result = json_decode($result,true);
      $result = $result['data']['link'];
      unset($result['name']);
      $user['link_url'] = $result;
      $user->save();
      return $result;
    } catch (Exception $e) {
      throw new Exception($e->getMessage(), 500);
    }
  }

  static public function commonUpdate($request)
  {
    try {

      return $result;
    } catch (Exception $e) {
      throw new Exception($e->getMessage(), 500);
    }
  }

}
