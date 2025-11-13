<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use app\api\logic\mini\CashOutLogic;

/**
 * 提现-控制器
 */
class CashOut extends Api
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
            'price',
            'bank_card_uuid',
            'site_id'=>1
        ]);
        $this->check($request,'CashOut.save');
        $result = CashOutLogic::Add($request,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
    public function index()
    {
        $request = $this->selectParam([
            'site_id'=>1,
            'page_index'=>1,
            'page_size'=>10
        ]);
        $result = CashOutLogic::List($request,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function read($id)
    {
        $result = CashOutLogic::Detail($id,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

}
