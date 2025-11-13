<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use app\api\logic\mini\BankCardLogic;

/**
 * 银行卡管理-控制器
 */
class BankCard extends Api
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
            'name',
            'phone',
            'number',
            'card_name',
            'site_id'=>1
        ]);
        $request['user_uuid'] = $this->userInfo['uuid'];
        $this->check($request,'BankCard.save');
        $result = BankCardLogic::Add($request,$this->userInfo);
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
            'is_default',
        ]);
        $result = BankCardLogic::List($request,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function read($id)
    {
        $result = BankCardLogic::Detail($id,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function update($id){
        $request = $this->selectParam([
            'name',
            'phone',
            'number',
            'card_name',
        ]);
        $request['user_uuid'] = $this->userInfo['uuid'];
        $request['uuid'] = $id;
        $this->check($request,'BankCard.save');
        $result = BankCardLogic::Edit($request,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function delete($id){
        $result = BankCardLogic::Delete($id,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }    }
}
