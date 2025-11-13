<?php

namespace app\api\logic\common;

use think\Exception;
use think\Db;
use think\Config;

/**
 * 根据卡号获取银行信息-逻辑
 */
class GetBankByNumLogic
{
    static public function commonAdd($request)
    {
        try {
            $access_token = self::getToken();
            $requestUrl = "https://api.weixin.qq.com/shop/funds/getbankbynum?access_token=".$access_token;
            $res = curl_post($requestUrl,json_encode(['account_number'=>$request['number']]));
            $jsonArray = json_decode($res, true);
            // 校验是否登陆成功
            if (isset($jsonArray['errcode']) && $jsonArray['errcode'] != 0) {
                return ['msg' => $jsonArray['errmsg']];
            }
            return $jsonArray['data'];
        } catch (Exception $e) {
            return ['msg' => $e->getMessage()];
        }
    }

    static function getToken(){
        //if(cache('access_token')){
            //return cache('access_token');
        //}
        $config = Config::get('wechat');
        $appid = $config['MinAppID'];
        $appSecret = $config['MinAppSecret'];
        //print_r($appid);exit;
        //print_r($appSecret);exit;
        $requestUrl = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$appSecret}";
        $res = curlSend($requestUrl);
        $jsonArray = json_decode($res, true);
        // 校验是否登陆成功
        if (isset($jsonArray['errcode'])) {
            return ['msg' => $jsonArray['errmsg']];
        }
        //cache('access_token', $jsonArray['access_token'],7000);
        return $jsonArray['access_token'];
    }

}
