<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\OrderLogic;

/**
 * 订单-控制器
 */
class OrderCancel extends Api
{
    public $restMethodList = 'put';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->miniValidateToken();
    }

    public function update($id)
    {
        $request = $this->selectParam([
            'reason',
        ]);
        $request['order_id'] = $id;
        $this->check($request,'Order.cancel');
        $result = OrderLogic::cancel($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }


}
