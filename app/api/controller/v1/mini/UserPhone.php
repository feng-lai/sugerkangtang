<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\UserLogic;

/**
 * 用户信息-控制器
 * User: Yacon
 * Date: 2022-07-21
 * Time: 14:31
 */
class UserPhone extends Api
{
    public $restMethodList = 'post';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->miniValidateToken();
    }

    public function index()
    {
        $result = UserLogic::miniList($this->userInfo);
        $this->render(200, ['result' => $result]);
    }

}
