<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\PartnerLogic;

/**
 * 合伙人团队成员-控制器
 */
class PartnerMember extends Api
{
    public $restMethodList = 'get';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function read($id)
    {
        $result = PartnerLogic::member($id);
        $this->render(200, ['result' => $result]);
    }

}
