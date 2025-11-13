<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use app\api\logic\cms\BankCardLogic;

/**
 * 银行卡-控制器
 */
class BankCard extends Api
{
    public $restMethodList = 'get';

    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function index()
    {
        $request = $this->selectParam([
            'site_id' => 1,
            'user_uuid',
            'number',
            'page_size' => 10,
            'page_index' => 1
        ]);
        $result = BankCardLogic::List($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
