<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use app\api\logic\cms\InvoiceLogic;

/**
 * 发票管理-控制器
 */
class Invoice extends Api
{
    public $restMethodList = 'get|post|put|delete';
    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function index()
    {
        $request = $this->selectParam([
            'site_id'=>1,
            'user_uuid',
            'keyword',
            'start_time',
            'end_time',
            'status',
            'page_size'=>10,
            'page_index'=>1
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
            'file',
            'note',
            'status'
        ]);
        $request['uuid'] = $id;
        $this->check($request,'Invoice.file_note');
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
