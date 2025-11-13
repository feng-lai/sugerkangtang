<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\ReasonLogic;

/**
 * 原因上移/下移-控制器
 */
class ReasonSetOrderNumber extends Api
{
    public $restMethodList = 'put';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }


    public function update($id)
    {
        $request = $this->selectParam([
            'type',
        ]);
        $request['uuid'] = $id;
        $this->check($request, "Reason.setOrderNumber");
        $result = ReasonLogic::setOrderNumber($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

}
