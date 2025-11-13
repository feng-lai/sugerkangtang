<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\AdminLogic;

/**
 * CAS同步-控制器
 */
class AdminSync extends Api
{
    public $restMethodList = 'get';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }



    public function index()
    {
        $result = AdminLogic::sync($this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }


}
