<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use app\api\model\UserToken;
use think\Exception;
use app\api\logic\mini\AgreementLogic;

/**
 * 协议中心-控制器
 */
class Agreement extends Api
{
    public $restMethodList = 'get|post|put|delete';

    public function index()
    {
        $request = $this->selectParam([
            'site_id'=>1,
            'type',
        ]);
        $result = AgreementLogic::List($request);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function read($id)
    {
        $result = AgreementLogic::Detail($id);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
