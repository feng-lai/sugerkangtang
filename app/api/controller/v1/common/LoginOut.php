<?php

namespace app\api\controller\v1\common;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\common\LoginOutLogic;
use app\api\model\AdminToken;
use app\api\model\UserToken;

/**
 * 后台登出-控制器
 */
class LoginOut extends Api
{
    public $restMethodList = 'get|post|put|delete';


    public function _initialize()
    {
        parent::_initialize();
    }

    public function index(){
        $request = $this->selectParam([
            'url',
            'type'=>1,
            'X-Access-Token'
        ]);
        $token = get_token();
        if($request['type'] == 1){
            $userInfo = AdminToken::build()->vali2($token);
        }else{
            $userInfo = UserToken::build()->vali2($token);
        }
        $result = LoginOutLogic::cmsList($request,$userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
