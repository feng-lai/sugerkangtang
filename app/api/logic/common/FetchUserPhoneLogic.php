<?php

namespace app\api\logic\common;

use app\api\model\User;
use app\common\wechat\Util;
use think\Config;
use \app\common\wechatmin\WechatDataCrypt;
use Exception;

/**
 * 获取用户手机号-逻辑
 * User: Yacon
 * Date: 2022-02-15
 * Time: 10:25
 */
class FetchUserPhoneLogic
{
  static public function commonAdd($request, $userInfo = null, $session_key = null)
  {
    try {
      $config = Config::get('wechat');
      if ($request['type'] == 'user') {
        $appid = $config['MinAppID'];
        $appSecret = $config['MinAppSecret'];
      } else {
        $appid = $config['AppMinAppID'];
        $appSecret = $config['AppMinAppSecret'];
      }


      $method = [
        '1' => 'old_logic',
        '2' => 'new_logic'
      ];

      if ($request['type'] == 'user') {
        $session_key = User::build()->where(['uuid' => $userInfo['uuid']])->value('session_key');
      } else {
        $session_key = $request['session_key'];
      }
      $request['userInfo'] = $userInfo;
      $request['session_key'] = $session_key;
      $request['appid'] = $appid;
      $request['appSecret'] = $appSecret;
      $result = call_user_func_array([self::class, $method[$request['v']]], [$request]);
      return $result;
    } catch (Exception $e) {
      return ['msg' => $e->getMessage()];
    }
  }

  // 旧版本获取手机号
  static function old_logic($request)
  {
    //获取session_key
    $requestUrl = "https://api.weixin.qq.com/sns/jscode2session";
    $requestUrl .= "?appid={$request['appid']}&secret={$request['session_key']}&js_code={$request['code']}&grant_type=authorization_code";
    $res = curlSend($requestUrl);
    $jsonArray = json_decode($res, true);
    // 解密手机号
    $pc = new WechatDataCrypt($request['appid'], $jsonArray['session_key']);
    $errCode = $pc->decryptData($request['encryptedData'], $request['iv'], $data);
    if ($errCode != 0) {
      return ['msg' => $errCode];
    }
    $data = json_decode($data, true);
    return $data['phoneNumber'];
  }

  // 新版本获取手机号
  static function new_logic($request)
  {
    $access_token = Util::getCurl("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$request['appid']}&secret={$request['appSecret']}");
    $access_token = objToArray(json_decode($access_token));
    $access_token = $access_token['access_token'];
    $url = "https://api.weixin.qq.com/wxa/business/getuserphonenumber?access_token=" . $access_token;
    $data = ['code' => $request['code']];
    $result = Util::postCurl($url, json_encode($data));
    $result =  objToArray(json_decode($result));
    return $result['phone_info']['purePhoneNumber'];
  }
}
