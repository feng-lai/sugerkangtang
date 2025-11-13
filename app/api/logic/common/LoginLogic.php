<?php

namespace app\api\logic\common;

use app\api\model\Admin;
use app\api\model\AdminToken;
use app\api\model\UserToken;
use app\api\model\User;
use think\Exception;

//引入文件
require_once ROOT_PATH . "/extend/cas_demo/CAS/CAS.php";

/**
 * 后台登陆-逻辑
 */
class LoginLogic
{

    static public function cmsAdd($request)
    {
        try {
            $result = Admin::build()->login($request['mobile'], $request['password']);
            return $result;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsList($request)
    {
        try {
            //指定log文件
            \phpCAS::setDebug(ROOT_PATH . './extend/cas_demo/log.log');
            //指定cas地址，最后一个true表示是否cas服务器为https，第二个参数为域名或是ip，第三个参数为服务器端口号，第四个参数为上下文路径
            \phpCAS::client(CAS_VERSION_2_0, 'sso.bit.edu.cn', 443, '/cas/login', true);
            \phpCAS::setNoCasServerValidation();
            \phpCAS::forceAuthentication();
            $user = \phpCAS::getUser();
            $ticket = uuid();
            $msg = '成功';
            if($request['type'] == 1){
                $admin = Admin::build()->where('mobile|number', $user)->update(['ticket' => $ticket]);
            }
            if($request['type'] == 2){
                $user = User::build()->where('mobile|number', $user)->update(['ticket' => $ticket]);
            }

            $request['url'] = urldecode($request['url']);
            if (preg_match('/\?/', $request['url'])) {
                header('Location: ' . $request['url'] . '&ticket=' . $ticket .'&msg=' . $msg);
            } else {
                header('Location: ' . $request['url'] . '?ticket=' . $ticket .'&msg=' . $msg);
            }
            exit;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
    static public function _getToken($request,$ticket){
        if($request['type'] == 1){
            $admin = Admin::build()->where('ticket', $ticket)->find();
            if($admin){
                $user_token = AdminToken::build()->where('admin_uuid',$admin->uuid)->find();
                if (!$user_token && !isset($user_token->token)){
                    $user_token = New AdminToken;
                    $user_token->uuid = uuid();
                    $user_token->token = uuid();
                    $user_token->admin_uuid = $admin['uuid'];
                    $user_token->expiry_time = date("Y-m-d H:i:s", time() + 604800);
                    $user_token->save();
                }else{
                    $user_token->token = uuid();
                    $user_token->expiry_time = date("Y-m-d H:i:s", time() + 604800);
                    $user_token->save();
                }
                $token = $user_token->token;
                //清除ticket
                Admin::build()->where('ticket', $ticket)->update(['ticket' => '']);
                if($admin->disabled == 2){
                    return ['msg'=>'该用户已被拉黑'];
                }
            }else{
                return ['msg'=>'用户不存在或者ticket失效'];
            }
        }else{
            $user = User::build()->where('ticket', $ticket)->find();
            if($user) {
                $user_token = UserToken::build()->where('user_uuid', $user->uuid)->find();
                // 如果Token不存在，新建token
                if (!$user_token && !isset($user_token->token)) {
                    $user_token = new UserToken;
                    $user_token->uuid = uuid();
                    $user_token->token = uuid();
                    $user_token->user_uuid = $user['uuid'];
                    $user_token->expiry_time = date("Y-m-d H:i:s", time() + 604800);
                    $user_token->save();
                } else {
                    $user_token->token = uuid();
                    $user_token->expiry_time = date("Y-m-d H:i:s", time() + 604800);
                    $user_token->save();
                }
                $token = $user_token->token;
                //清除ticket
                User::build()->where('ticket', $ticket)->update(['ticket' => '']);
            }else{
                return ['msg'=>'用户不存在或者ticket失效'];
            }
        }
        return ['token'=>$token];

    }
}
