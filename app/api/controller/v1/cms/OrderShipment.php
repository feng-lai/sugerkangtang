<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\OrderLogic;

/**
 * 订单发货-控制器
 */
class OrderShipment extends Api
{
    public $restMethodList = 'post';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function save()
    {
        $request = $this->selectParam([
            'shipment_info',
        ]);
        $result = OrderLogic::Shipment($request, $this->userInfo);

        $this->render(200, ['result' => $result]);
    }

}
