<?php
/**
 * Created by PhpStorm.
 * User: fio
 * Date: 2017/2/21
 * Time: 下午4:47
 */

namespace app\common\tools;

use think\Config;
use think\Exception;

class wechatMsg
{
    public function handle($template_id,$page = '',$touser,$data)
    {
        try {
            $access_token = $this->getToken();
            $requestUrl = "https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token=".$access_token;
            $res = curl_post($requestUrl,json_encode([
                'touser'=>$touser,
                'template_id'=>$template_id,
                'page'=>$page,
                'data'=>$data,
            ]));
            $jsonArray = json_decode($res, true);
            // 校验是否登陆成功
            if (isset($jsonArray['errcode']) && $jsonArray['errcode'] != 0) {
                return ['msg' => $jsonArray['errmsg']];
            }
            return $jsonArray;
        } catch (Exception $e) {
            return ['msg' => $e->getMessage()];
        }
    }
    public function getToken(){
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
