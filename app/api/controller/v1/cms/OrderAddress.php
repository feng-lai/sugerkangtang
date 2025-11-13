<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\OrderAddressLogic;

/**
 * 订单地址-控制器
 */
class OrderAddress extends Api
{
  public $restMethodList = 'get|post|put|delete';


  public function _initialize()
  {
    parent::_initialize();
    $this->userInfo = $this->cmsValidateToken();
  }


  public function read($id)
  {
    $result = OrderAddressLogic::cmsDetail($id,$this->userInfo);
    $this->render(200, ['result' => $result]);
  }


  public function update($id)
  {
    $request = $this->selectParam([
        'name',
        'phone',
        'address',
        'province',
        'city',
        'district',
        'tag',
    ]);
    $request['uuid'] = $id;
    $this->check($request, "OrderAddress.save");
    $result = OrderAddressLogic::cmsEdit($request,$this->userInfo);
    if (isset($result['msg'])) {
      $this->returnmsg(400, [], [], '', '', $result['msg']);
    } else {
      $this->render(200, ['result' => $result]);
    }
  }

}
