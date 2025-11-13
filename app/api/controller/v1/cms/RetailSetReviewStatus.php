<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\RetailLogic;

/**
 * 分销员设置审核状态-控制器
 */
class RetailSetReviewStatus extends Api
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
            'review_status',
            'certificate',
            'note',
            'cash_out_persent',
            'cash_out_low',
        ]);
        $request['uuid'] = $id;
        $this->check($request, "Retail.setReviewStatus");
        $result = RetailLogic::setReviewStatus($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }


}
