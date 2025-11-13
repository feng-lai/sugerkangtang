<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use app\api\logic\cms\CommissionOrderLogic;

/**
 * 推广订单-控制器
 */
class CommissionOrder extends Api
{
    public $restMethodList = 'get|post|put|delete';
    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function save()
    {
        $request = $this->selectParam([
            'site_id'=>1,
            'parameter',
            'status'
        ]);
        $this->check($request,'CommissionOrder.save');
        $result = CommissionOrderLogic::Add($request,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }    }

    public function index()
    {
        $request = $this->selectParam([
            'site_id'=>1,
            'user_uuid',
            'id',
            'name',
            'start_time',
            'end_time',
            'status',
            'order_id',
            'page_size'=>10,
            'page_index'=>1,
            'retail_uuid'
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
        $result = CommissionOrderLogic::Detail($id,$this->userInfo);
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
            'address',
            'province',
            'city',
            'district',
            'tag',
            'is_default'
        ]);
        $request['uuid'] = $id;
        $this->check($request,'Address.save');
        $result = CommissionOrderLogic::Edit($request,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function delete($id){
        $result = CommissionOrderLogic::Delete($id,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }    }
}
