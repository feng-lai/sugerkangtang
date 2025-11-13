<?php

namespace app\api\logic\common;

use app\api\model\UserToken;
use app\api\model\User;
use think\Exception;
use think\Db;
use think\Config;

/**
 * 登录-逻辑
 * User: Yacon
 * Date: 2022-02-15
 * Time: 10:36
 */
class loginByPasswordLogic
{
  static public function loginByPassword($request)
  {
    try {
        Db::startTrans();
        //$user = User::where(['mobile' => $request['mobile'], 'is_deleted' => 1, 'password'=>md6($request['password'])])->find();
        $user = User::where(['mobile' => $request['user_name'], 'is_deleted' => 1, 'password'=>$request['password']])->find();
        if (null == $user) {
            $user = User::where(['number' => $request['user_name'], 'is_deleted' => 1, 'password'=>$request['password']])->find();
        }
        if (null == $user) {
            throw new Exception('用户不存在或者密码错误');
        }
        if ($user['disabled'] == 2) {
            throw new Exception('您已被拉黑，无法登陆');
        }
        $user['update_time'] = date("Y-m-d H:i:s", time());
        $user['last_login_time'] = date("Y-m-d H:i:s", time());
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
        Db::commit();
        return ['token' => $userToken['token'], 'user' => $user];
    } catch (Exception $e) {
        Db::rollback();
        return ['msg' => $e->getMessage()];
    }
  }
}
