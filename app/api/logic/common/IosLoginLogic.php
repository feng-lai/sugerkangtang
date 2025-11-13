<?php

namespace app\api\logic\common;

use AlibabaCloud\SDK\Dypnsapi\V20170525\Models\GetSmsAuthTokensResponseBody\data;
use app\api\model\ActivitiesTurntable;
use app\api\model\UserToken;
use app\api\model\User;
use app\api\model\WechatLogin;
use app\api\model\UserRelation;
use think\Exception;
use think\Db;
use think\Config;
use app\exception\BaseException;
use AppleSignIn\ASDecoder;
use app\api\model\Captcha;

/**
 * 苹果登录-逻辑
 * User:
 * Date:
 * Time:
 */
class IosLoginLogic
{
  static public function commonAdd($request)
  {
    try {
      $clientUser = $request['apple_union_id'];
      $identityToken = $request['identity_token'];
      $appleSignInPayload = ASDecoder::getAppleSignInPayload($identityToken);
      $isValid = $appleSignInPayload->verifyUser($clientUser);
      if(!$isValid){
        return ['msg'=>'授权登录失败'];
      }
      if(!$request['mobile']){
        $user = User::build()->where('apple_union_id',$request['apple_union_id'])->find();
        if($user){
          if ($user->disabled == 2) {
            return ['msg'=>'您已被禁用，无法登陆'];
          }
          $user->last_login_time = now_time(time());
          $user->save();
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
          return ['token' => $userToken['token'], 'user' => $user];
        }else{
          return ['msg'=>'请绑定手机号'];
        }
      }else{
        if(!$request['v_code']){
          return ['msg'=>'验证码不能为空'];
        }
        //判断验证码
        Captcha::build()->captchaCheck(['mobile' => $request['mobile'], 'code' => $request['v_code']]);

        $user = User::build()->where('mobile',$request['mobile'])->find();
        if($user){
          $user->apple_union_id = $request['apple_union_id'];
          $user->last_login_time = now_time(time());
          $user->save();
          //绑定分享关系
          if($request['user_uuid']){
            UserRelation::build()->to_relation($request['user_uuid'],$user['uuid']);
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
          return ['token' => $userToken['token'], 'user' => $user];
        }else{
          //新增
          $result = self::user_login(['apple_union_id'=>$request['apple_union_id'],'mobile'=>$request['mobile'],'user_uuid'=>$request['user_uuid']]);
          return $result;
        }
      }
    } catch (Exception $e) {
      throw new Exception($e->getMessage(), 500);
    }
  }

  // 用户端登录逻辑
  static function user_login($jsonArray)
  {
    try {
      Db::startTrans();

      $number = User::build()->createUserID();
      $user = [
        'uuid' => uuid(),
        'apple_union_id' => $jsonArray['apple_union_id'],
        'mobile' => $jsonArray['mobile'],
        'user_id' => $number[1],
        'serial_number' => $number[0],
        'last_login_time'=> date("Y-m-d H:i:s", time()),
        'create_time' => date("Y-m-d H:i:s", time()),
        'update_time' => date("Y-m-d H:i:s", time()),
      ];
      User::build()->insert($user);
      //绑定分享关系
      if($jsonArray['user_uuid']){
        UserRelation::build()->to_relation($jsonArray['user_uuid'],$user['uuid']);
      }
      $user = User::build()->where(['apple_union_id' => $jsonArray['apple_union_id']])->find();

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
      return ['token' => $userToken['token'], 'user' => $user];
    } catch (Exception $e) {
      Db::rollback();
      return ['msg' => $e->getMessage()];
    }
  }
}
