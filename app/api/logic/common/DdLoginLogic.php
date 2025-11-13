<?php

namespace app\api\logic\common;

use app\api\model\UserToken;
use app\api\model\User;
use think\Exception;
use app\common\tools\Dingding;

/**
 * 后台登陆-逻辑
 */
class DdLoginLogic
{

    static public function add($request)
    {
        try {
            $number = Dingding::build()->getData($request['code']);
            $user = User::build()->where('number', $number)->find();
            if ($user) {
                $user_token = UserToken::build()->where(['user_uuid' => $user['uuid']])->find();
                // 如果Token不存在，新建token
                if (!$user_token){
                    $user_token = New UserToken;
                    $user_token->uuid = uuid();
                    $user_token->user_uuid = $user['uuid'];
                    $user_token->expiry_time = date("Y-m-d H:i:s", time() + 604800);
                    $user_token->save();
                }else{
                    $user_token->token = uuid();
                    $user_token->expiry_time = date("Y-m-d H:i:s", time() + 604800);
                    $user_token->save();
                }
                $token = $user_token->token;
                if($user->disabled == 2){
                    return ['msg'=>'该用户已被拉黑'];
                }
                return ['token'=>$token];
            }else{
                return ['msg'=>'无此用户'];
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

}
