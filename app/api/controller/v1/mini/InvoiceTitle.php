<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use app\api\logic\mini\InvoiceTitleLogic;

/**
 * 发票抬头-控制器
 */
class InvoiceTitle extends Api
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
            'type',
            'title',
            'number',
            'bank',
            'bank_number',
            'address',
            'phone',
            'site_id'=>1
        ]);
        $this->check($request,'InvoiceTitle.save');
        $result = InvoiceTitleLogic::Add($request,$this->userInfo);
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
            'type',
        ]);
        $result = InvoiceTitleLogic::List($request,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function read($id)
    {
        $result = InvoiceTitleLogic::Detail($id,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function update($id){
        $request = $this->selectParam([
            'type',
            'title',
            'number',
            'bank',
            'bank_number',
            'address',
            'phone',
        ]);
        $request['uuid'] = $id;
        $this->check($request,'InvoiceTitle.save');
        $result = InvoiceTitleLogic::Edit($request,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function delete($id){
        $result = InvoiceTitleLogic::Delete($id,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }    }
}
