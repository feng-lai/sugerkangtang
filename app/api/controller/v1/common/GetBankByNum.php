<?php

namespace app\api\controller\v1\common;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\common\GetBankByNumLogic;

/**
 * 根据卡号获取银行信息-控制器
 */
class GetBankByNum extends Api
{
    public $restMethodList = 'get|post|put|delete';

    public function save()
    {
        $request = $this->selectParam([
            'number',
        ]);
        $this->check($request, "GetBankByNum.save");
        $result = GetBankByNumLogic::commonAdd($request);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
