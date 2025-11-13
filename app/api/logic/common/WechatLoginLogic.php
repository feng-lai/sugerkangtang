<?php

namespace app\api\logic\common;

use AlibabaCloud\SDK\Dypnsapi\V20170525\Models\GetSmsAuthTokensResponseBody\data;
use app\api\model\ActivitiesTurntable;
use app\api\model\Captcha;
use app\api\model\Partner;
use app\api\model\UserToken;
use app\api\model\Employee;
use app\api\model\EmployeeToken;
use app\api\model\Interest;
use app\api\model\User;
use app\api\model\UserInterrest;
use app\api\model\WechatLogin;
use app\api\model\Contestant;
use app\api\model\UserRelation;
use think\Exception;
use think\Db;
use think\Config;
use app\exception\BaseException;

/**
 * 微信小程序登录-逻辑
 */
class WechatLoginLogic
{
    static public function commonAdd($request)
    {
        try {
            //$result = self::user_login(['openid'=>'oyI151xAv8yEOIlLrBDZHD5JqmCs'],$request);
            //return $result;
            $config = Config::get('wechat');
            $appid = $config['MinAppID'];
            $appSecret = $config['MinAppSecret'];
            $requestUrl = "https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$appSecret}&js_code={$request['code']}&grant_type=authorization_code";
            $res = curlSend($requestUrl);
            $jsonArray = json_decode($res, true);
            // 校验是否登陆成功
            if (isset($jsonArray['errcode'])) {
                return ['msg' => $jsonArray['errmsg']];
            }
            $result = self::user_login($jsonArray,$request);
            return $result;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    // 用户端登录逻辑
    static function user_login($jsonArray,$request)
    {
        try {
            Db::startTrans();
            // 根据openid查询用户
            $user = User::build()->where(['openid' => $jsonArray['openid'], 'is_deleted' => 1])->find();
            // 已注册，更新用户的登录会话key
            $invite = '';
            if($request['user_uuid']){
                $invite = Partner::build()->where('user_uuid',$request['user_uuid'])->value('user_uuid');
            }

            if ($user) {
                if ($user['disabled'] == 2) {
                    if( !$user['disabled_end_time'] || strtotime($user['disabled_end_time']) > time()){
                        throw new Exception('您已被禁用，无法登陆');
                    }
                }
                if($request['user_uuid'] && !$user['invite_partner_uuid']){
                    $user['invite_partner_uuid'] = $invite?$request['user_uuid']:'';
                }
                $user['phone'] = $request['phone'];
                $user['update_time'] = date("Y-m-d H:i:s", time());
                $user['last_login_time'] = date("Y-m-d H:i:s", time());
                $user->save();
            } // 未注册，则新增用户
            else {
                if(User::build()->where(['phone'=>$request['phone'],'is_deleted'=>1])->find()){
                    throw new Exception('该手机号已被使用');
                }

                $user = [
                    'uuid' => uuid(),
                    'openid' => $jsonArray['openid'],
                    'site_id' => $request['site_id'],
                    'phone' => $request['phone'],
                    'name'=>'TKT'.getNumberOne('6'),
                    'invite_partner_uuid'=>$invite?$request['user_uuid']:'',
                    'last_login_time' => date("Y-m-d H:i:s", time()),
                    'create_time' => date("Y-m-d H:i:s", time()),
                    'update_time' => date("Y-m-d H:i:s", time()),
                ];
                User::build()->insert($user);
                $user = User::build()->where(['openid' => $jsonArray['openid']])->find();
            }

            // 更新用户token
            $userToken = UserToken::build()->where('user_uuid', $user['uuid'])->find();
            if (null == $userToken) {
                $userToken = UserToken::build();
                $userToken->uuid = uuid();
                $userToken->token = uuid();
                $userToken->user_uuid = $user['uuid'];
                $userToken->create_time = date("Y-m-d H:i:s", time());
            }
            $userToken->expiry_time = date("Y-m-d H:i:s", time() + 3600 * 24 * 90);
            $userToken->update_time = date("Y-m-d H:i:s", time());
            $userToken->save();
            Db::commit();
            return ['token' => $userToken['token']];
        } catch (Exception $e) {
            Db::rollback();
            return ['msg' => $e->getMessage()];
        }
    }
}
