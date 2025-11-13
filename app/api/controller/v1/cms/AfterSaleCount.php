<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\AfterSaleLogic;

/**
 * 售后状态统计-控制器
 */
class AfterSaleCount extends Api
{
    public $restMethodList = 'get';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function index()
    {
        $result = AfterSaleLogic::after_sale_count($this->userInfo);

        $this->render(200, ['result' => $result]);
    }
}
