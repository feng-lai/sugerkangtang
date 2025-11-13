<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use app\api\model\UserToken;
use think\Exception;
use app\api\logic\mini\MsgLogic;

/**
 * 消息-控制器
 */
class Msg extends Api
{
    public $restMethodList = 'get|put';
    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->miniValidateToken();
    }
    public function index()
    {
        $request = $this->selectParam([
            'page_index'=>1,
            'page_size'=>10,
            'site_id'=>1,
            'type',
        ]);
        $result = MsgLogic::List($request,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function read($id)
    {
        $result = MsgLogic::Detail($id,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function update($id)
    {
        $result = MsgLogic::Edit($id,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
