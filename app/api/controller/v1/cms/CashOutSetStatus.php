<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use app\api\logic\cms\CashOutLogic;

/**
 * 提现审核-控制器
 */
class CashOutSetStatus extends Api
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
            'status',
            'reason',
            'img'
        ]);
        $request['cash_out_id'] = $id;
        $this->check($request, 'CashOut.setStatus');
        $result = CashOutLogic::setStatus($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

}
