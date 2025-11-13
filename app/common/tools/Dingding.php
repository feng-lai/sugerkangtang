<?php

namespace app\common\tools;

use think\Cache;

use think\Db;

use think\Exception;

use app\common\tools\Sync;


class Dingding
{
    private $get_token_url = 'https://api.dingtalk.com/v1.0/oauth2/accessToken';
    private $user_info_url = 'https://oapi.dingtalk.com/topapi/v2/user/getuserinfo';
    private $accessToken = '';
    private $AppKey = 'dingcvzxr40aevog2xvi';
    private $AppSecret = 'lst_aq_YwRYnq1qrrAejfLcQ4slB38QfAZkxwQxIrPrO4MBcNmG8Ez7B76IwzvvG';
    private $userid = '';
    private $token = '';

    public static function build()
    {
        return new self();
    }

    /**
     * Author: Administrator
     */
    public function getData($code)
    {
        $this->getToken();
        $this->getUserInfo($code);
        return Sync::build()->getInfo($this->userid);
    }

    private function getUserInfo($code){
        $res = $this->post_url($this->user_info_url.'?access_token='.$this->accessToken,json_encode(['code' => $code]),['Content-Type: application/json']);
        if($res['errcode'] == 0){
            $this->userid = $res['result']['userid'];
        }else{
            throw new Exception($res['errmsg'], 500);
        }
    }

    private function getToken()
    {
        $token = Cache::get('dingDing_accessToken');
        if ($token) {
            $this->accessToken = $token;
            return $token;
        }

        $token = $this->post_url($this->get_token_url,json_encode(['appKey' => $this->AppKey,'appSecret' => $this->AppSecret]) ,['Content-Type: application/json']);
        if ($token) {
            Cache::set('dingDing_accessToken', $token['accessToken'], $token['expireIn']);
        }
        $this->accessToken = $token['accessToken'];
        return $token['accessToken'];
    }


    private function post_url($url, $jsonData, $header = '')
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url); // 目标URL
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 返回结果而不是直接输出
            curl_setopt($ch, CURLOPT_POST, true); // 设置为POST请求
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData); // 设置POST字段为JSON格式
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            $response = curl_exec($ch);
            curl_close($ch);
            $data = json_decode($response, true);
            return $data;
        } catch (Exception $e) {
            return null;
        }
    }
}