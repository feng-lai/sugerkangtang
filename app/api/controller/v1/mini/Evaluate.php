<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\EvaluateLogic;

/**
 * 评价-控制器
 */
class Evaluate extends Api
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
            'admin_uuid',
            'content',
            'img',
            'point',
            'anonymous',
            'course_uuid'
        ]);
        $this->check($request, "Evaluate.save");
        $result = EvaluateLogic::Add($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }


    public function read($id)
    {
        $result = EvaluateLogic::Detail($id,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function delete($id)
    {
        $result = EvaluateLogic::Delete($id, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
