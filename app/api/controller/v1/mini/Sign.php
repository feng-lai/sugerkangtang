<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\SignLogic;

/**
 * 签到-控制器
 */
class Sign extends Api
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
            'course_uuid'
        ]);
        $this->check($request, "Order.save");
        $result = SignLogic::miniAdd($request,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

}
