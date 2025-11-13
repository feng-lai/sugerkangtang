<?php

namespace app\api\logic\common;

use app\api\model\Admin;
use app\api\model\AdminToken;
use app\api\model\UserToken;
use think\Exception;
//引入文件
require_once ROOT_PATH."/extend/cas_demo/CAS/CAS.php";

/**
 * 后台登出-逻辑
 */
class LoginOutLogic
{

    static public function cmsList($request,$userInfo){
        //print_r(urlencode('http://127.0.0.1/v1/common/Login?url=127.0.0.1&type=2'));exit;
        //print_r($request);exit;
        //清除token
        if($userInfo){
            if($request['type'] == 1){
                AdminToken::build()->where('admin_uuid',$userInfo['uuid'])->delete();
            }else{
                UserToken::build()->where('user_uuid',$userInfo['uuid'])->delete();
            }
        }
        //指定log文件
        \phpCAS::setDebug(ROOT_PATH.'./extend/cas_demo/log.log');
        //指定cas地址，最后一个true表示是否cas服务器为https，第二个参数为域名或是ip，第三个参数为服务器端口号，第四个参数为上下文路径
        \phpCAS::client(CAS_VERSION_2_0,'login.bit.edu.cn',443,'cas',true);
        \phpCAS::handleLogoutRequests();
        $param = array('service'=>$request['url']);
        \phpCAS::logout($param);
        exit;
    }
}
