<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\OrderLogic;

/**
 * 订单统计-控制器
 */
class OrderCount extends Api
{
    public $restMethodList = 'get|post|put|delete';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function index()
    {
        $result = OrderLogic::order_count($this->userInfo);

        $this->render(200, ['result' => $result]);
    }

}
