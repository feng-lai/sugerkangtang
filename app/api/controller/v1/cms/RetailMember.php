<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\RetailLogic;

/**
 * 分销员团队成员-控制器
 */
class RetailMember extends Api
{
    public $restMethodList = 'get';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function read($id)
    {
        $result = RetailLogic::member($id);
        $this->render(200, ['result' => $result]);
    }

}
