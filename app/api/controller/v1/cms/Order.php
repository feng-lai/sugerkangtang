<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\OrderLogic;

/**
 * 报名-控制器
 */
class Order extends Api
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
            'keyword',
            'page_size' => 10,
            'page_index' => 1,
            'user_info',
            'start_time',
            'end_time',
            'status',
            'user_uuid'
        ]);
        $result = OrderLogic::cmsList($request, $this->userInfo);

        $this->render(200, ['result' => $result]);
    }

    public function read($id)
    {
        $result = OrderLogic::cmsDetail($id, $this->userInfo);
        $this->render(200, ['result' => $result]);
    }

    public function delete($id)
    {
        $request = $this->selectParam([
            'reason'
        ]);
        $request['uuid'] = $id;
        $result = OrderLogic::cmsDelete($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
