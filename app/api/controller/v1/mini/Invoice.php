<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use app\api\logic\mini\InvoiceLogic;

/**
 * 发票-控制器
 */
class Invoice extends Api
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
            'order_id',
            'number',
            'bank',
            'bank_number',
            'address',
            'phone',
            'site_id'=>1
        ]);
        $this->check($request,'Invoice.save');
        $result = InvoiceLogic::Add($request,$this->userInfo);
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
        $result = InvoiceLogic::List($request,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function read($id)
    {
        $result = InvoiceLogic::Detail($id,$this->userInfo);
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
        $this->check($request,'Invoice.edit');
        $result = InvoiceLogic::Edit($request,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function delete($id){
        $result = InvoiceLogic::Delete($id,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }    }
}
