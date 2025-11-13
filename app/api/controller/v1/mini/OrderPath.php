<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;


/**
 * 订单-查看物流-控制器
 */
class OrderPath extends Api
{
    public $restMethodList = 'get|post|put|delete';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->miniValidateToken();
    }

    public function index()
    {
        $request = $this->selectParam([
            'order_id',
        ]);
        $order = \app\api\model\Order::build()->where('order_id', $request['order_id'])->where('user_uuid',$this->userInfo['uuid'])->findOrFail();
        if(!$order->num){
            $this->render(200, ['result' => []]);
        }
        \app\api\model\OrderPath::build()->getPath(['com' => $order->com, 'num' => $order->num, 'order_id' => $request['order_id']]);
        $result = \app\api\model\OrderPath::build()->field('time,context,status')->where('order_id', $request['order_id'])->select();
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }


}
