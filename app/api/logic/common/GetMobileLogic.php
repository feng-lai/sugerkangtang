<?php

namespace app\api\logic\common;

use think\Exception;
use think\Db;
use think\Config;

/**
 * 获取手机号码-逻辑
 * User: Yacon
 * Date: 2022-02-15
 * Time: 10:36
 */
class GetMobileLogic
{
    static public function commonAdd($request)
    {
        try {
            $access_token = self::getToken();
            $requestUrl = "https://api.weixin.qq.com/wxa/business/getuserphonenumber?access_token=".$access_token;
            $res = curl_post($requestUrl,json_encode(['code'=>$request['code']]));
            $jsonArray = json_decode($res, true);
            // 校验是否登陆成功
            if (isset($jsonArray['errcode']) && $jsonArray['errcode'] != 0) {
                return ['msg' => $jsonArray['errmsg']];
            }
            return $jsonArray['phone_info'];
        } catch (Exception $e) {
            return ['msg' => $e->getMessage()];
        }
    }

    static function getToken(){
        $config = Config::get('wechat');
        $appid = $config['MinAppID'];
        $appSecret = $config['MinAppSecret'];
        $requestUrl = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$appSecret}";
        $res = curlSend($requestUrl);
        $jsonArray = json_decode($res, true);
        // 校验是否登陆成功
        if (isset($jsonArray['errcode'])) {
            return ['msg' => $jsonArray['errmsg']];
        }
        return $jsonArray['access_token'];
    }

}
