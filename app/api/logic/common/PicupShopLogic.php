<?php

namespace app\api\logic\common;

use app\api\model\PicupShop;
use think\Exception;
use think\Db;

/**
 * 皮卡图片处理-逻辑
 * User: Yacon
 * Date: 2023-03-28
 * Time: 20:30
 */
class PicupShopLogic
{

  static public function commonList($request, $userInfo)
  {
    $url = "https://picupapi.tukeli.net/api/v1/mattingByUrl?mattingType={$request['mattingType']}&url={$request['url']}&crop={$request['crop']}&bgcolor={$request['bgcolor']}&faceAnalysis={$request['faceAnalysis']}&outputFormat={$request['outputFormat']}";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('APIKEY: 343dc18cf966403081a149931f1c2693'));
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response);
  }
}
