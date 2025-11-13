<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\OrderPathLogic;

/**
 * 查看物流-控制器
 */
class OrderPath extends Api
{
    public $restMethodList = 'get|put';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function index()
    {
        $request = $this->selectParam([
            'order_id'
        ]);
        $this->check($request,'OrderPath.list');
        $result = OrderPathLogic::cmsList($request,$this->userInfo);
        $this->render(200, ['result' => $result]);
    }

    public function update($id)
    {
        $request = $this->selectParam([
            'com',
            'num',
            'com_name',
            'order_path_note'
        ]);
        $request['order_id'] = $id;
        $this->check($request,'OrderPath.save');
        $result = OrderPathLogic::cmsEdit($request,$this->userInfo);
        $this->render(200, ['result' => $result]);
    }
}
