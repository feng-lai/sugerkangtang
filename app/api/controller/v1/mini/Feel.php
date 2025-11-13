<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\FeelLogic;

/**
 * 心得-控制器
 */
class Feel extends Api
{
    public $restMethodList = 'get|post|put|delete';

    public function _initialize()
    {
        parent::_initialize();

    }

    public function index()
    {
        $request = $this->selectParam([
            'page_size' => 10,
            'page_index' => 1,
            'order_uuid',
            'course_uuid'
        ]);
        $result = FeelLogic::List($request);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function read($id)
    {
        $result = FeelLogic::Detail($id, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function save()
    {
        $this->userInfo = $this->miniValidateToken();
        $request = $this->selectParam([
            'order_uuid',
            'content',
            'img',
            'anonymous'
        ]);
        $this->check($request, "Feel.save");
        $result = FeelLogic::Add($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

}
