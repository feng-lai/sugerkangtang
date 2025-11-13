<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\OrderLogic;

/**
 * 订单-控制器
 */
class Order extends Api
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
            'keyword',
            'status',
            'page_size'=>10,
            'page_index'=>1,
            'start_time',
            'end_time',
        ]);
        $result = OrderLogic::List($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function read($id)
    {
        $result = OrderLogic::Detail($id, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function save()
    {
        $request = $this->selectParam([
            'address_uuid',
            'product',
            'medical_report_uuid',
            'note',
            'type',
            'site_id'=>1
        ]);
        $this->check($request, "Order.save");
        $result = OrderLogic::Add($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function delete($id)
    {
        $result = OrderLogic::Delete($id, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

}
