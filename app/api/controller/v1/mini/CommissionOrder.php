<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use app\api\logic\mini\CommissionOrderLogic;

/**
 * 推广订单-控制器
 */
class CommissionOrder extends Api
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
            'site_id'=>1,
            'page_size'=>10,
            'page_index'=>1,
            'keyword',
            'status'
        ]);
        $result = CommissionOrderLogic::List($request,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function read($id)
    {
        $result = AddressLogic::Detail($id,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

}
