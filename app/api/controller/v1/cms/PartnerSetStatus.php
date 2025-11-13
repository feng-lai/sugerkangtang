<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\PartnerLogic;

/**
 * 合伙人设置状态-控制器
 */
class PartnerSetStatus extends Api
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
        ]);
        $request['uuid'] = $id;
        $this->check($request, "Retail.setStatus");
        $result = PartnerLogic::setStatus($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }


}
