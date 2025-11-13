<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\PartnerReviewLogic;

/**
 * 合伙人审核设置备注-控制器
 */
class PartnerReviewSetNote extends Api
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
            'note',
        ]);
        $request['uuid'] = $id;
        $this->check($request, "PartnerReview.setNote");
        $result = PartnerReviewLogic::setNote($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }


}
