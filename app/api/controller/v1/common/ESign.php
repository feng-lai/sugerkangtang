<?php

namespace app\api\controller\v1\common;

use app\api\controller\Api;
use app\api\logic\common\ESignLogic;

class ESign extends Api
{
    public $restMethodList = 'get|post|put|delete';
    public function save()
    {
        $request = $this->selectParam([
            'retail_uuid',
            'bank_card_uuid',
            'redirect_url'
        ]);
        $result = ESignLogic::commonAdd($request);
        if (isset($result['msg'])) {
            $this->returnmsg(400, '', [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
