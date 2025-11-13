<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use app\api\logic\mini\ESignLogic;

class ESign extends Api
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
            'bank_card_uuid',
            'redirect_url'
        ]);

        $this->check($request,'ESign.save');
        $result = ESignLogic::commonAdd($request,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, '', [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
