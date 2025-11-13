<?php

namespace app\api\logic\common;

use app\api\model\UserRelation;
use app\api\model\UserToken;
use app\api\model\User;
use app\api\model\Contestant;
use think\Exception;
use think\Db;
use think\Config;
use app\api\model\Captcha;
use app\api\logic\common\WechatLoginLogic;

/**
 * 登录-逻辑
 * User: Yacon
 * Date: 2022-02-15
 * Time: 10:36
 */
class loginByCodeLogic
{
  static public function loginByCode($request)
  {
    try {
        //判断验证码
        Captcha::build()->captchaCheck(['code'=>$request['code'],'mobile'=>$request['phone']]);

        $config = Config::get('wechat');
        $appid = $config['MinAppID'];
        $appSecret = $config['MinAppSecret'];
        $requestUrl = "https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$appSecret}&js_code={$request['wx_code']}&grant_type=authorization_code";
        $res = curlSend($requestUrl);
        $jsonArray = json_decode($res, true);
        // 校验是否登陆成功
        if (isset($jsonArray['errcode'])) {
            return ['msg' => $jsonArray['errmsg']];
        }

        $result = WechatLoginLogic::user_login($jsonArray,$request);
        return $result;
    } catch (Exception $e) {
        Db::rollback();
        return ['msg' => $e->getMessage()];
    }
  }
}
