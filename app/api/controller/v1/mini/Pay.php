<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\PayLogic;

/**
 * 订单付款-控制器
 * User:
 * Date: 2022-07-21
 * Time: 14:31
 */
class Pay extends Api
{
    public $restMethodList = 'get|post|put|delete';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->miniValidateToken();
    }

    public function save()
    {
        $request = $this->selectParam([
            'order_id', // 订单号
        ]);
        $this->check($request, "Pay.save");
        $result = PayLogic::save($request, $this->userInfo);
        if (isset($result['msg'])) {
            //未支付成功通知
            \app\api\model\Order::build()->pendding_msg($request['order_id']);
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {

            $this->render(200, ['result' => $result]);
        }
    }

    public function update($id)
    {
        $request = $this->selectParam([]);
        $request['uuid'] = $id;
        $result = OrderLogic::miniEdit($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    // public function delete($id){
    //   $result = UserLogic::miniDelete($id,$this->userInfo);
    //   if (isset($result['msg'])) {
    //     $this->returnmsg(400, [], [], '', '', $result['msg']);
    //   } else {
    //     $this->render(200, ['result' => $result]);
    //   }
    // }
}
