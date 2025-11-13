<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\ParameterLogic;

/**
 * 商品参数设置状态-控制器
 */
class ParameterSetStatus extends Api
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
        $this->check($request, "Parameter.setStatus");
        $result = ParameterLogic::setStatus($request, $this->userInfo, $id);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }



}
