<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use app\api\logic\cms\AddressLogic;

/**
 * 收货地址设置默认-控制器
 */
class AddressSetDefault extends Api
{
    public $restMethodList = 'put';

    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }


    public function update($id)
    {
        $result = AddressLogic::setDefault($id, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

}
