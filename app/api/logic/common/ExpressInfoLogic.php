<?php

namespace app\api\logic\common;

use think\Exception;


/**
 * 物流信息-逻辑
 * User: Yacon
 * Date: 2022-03-13
 * Time: 23:50
 */
class ExpressInfoLogic
{
  static public function commonAdd($request, $userInfo)
  {
    $customer = "ACCDAEA18BA63D3446AD631347EBDAA8";
    $key = "KfcGYUWr9618";
    $num = $request['num'];
    // 提供了快递公司的编码
    if ($request['com']) {
      $com = $request['com'];
    }
    // 未提供快递公司的编码
    else {
      $comInfo = self::getCom($key, $num);
      $com = $comInfo['comCode'];
      $result['name'] = $comInfo['name'];
    }
    $phone = $request['phone'] ?? '';
    $info = self::query($key, $customer, $num, $com, $phone);
    $result['data'] = $info['data'];
    $result['state'] = $info['state'];
    return $result;
  }


  /**
   * 快递100查询实时快递
   */
  static public function query($key, $customer, $num, $com, $phone = "")
  {
    $param = json_encode(["com" => $com, "num" => "$num", "phone" => $phone]);
    $sign = strtoupper(md5("{$param}{$key}{$customer}"));

    $url = "http://poll.kuaidi100.com/poll/query.do?customer={$customer}&sign={$sign}&param={$param}";
    $result = curl_post($url, '');
    $result = objToArray(json_decode($result));
    if (!is_array($result)) {
      throw new Exception($result['message']);
    }
    return $result;
  }

  /**
   * 快递100根据单号查询快递公司的编码
   */
  static public function getCom($key, $num)
  {
    $url = "http://www.kuaidi100.com/autonumber/auto?num={$num}&key={$key}";
    $result = curl_send($url);
    $result = objToArray(json_decode($result));
    if (!is_array($result)) {
      throw new Exception($result['message']);
    }
    return $result[0];
  }
}
