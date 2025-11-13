<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\AdminLogic;

/**
 * 管理员设置权限-控制器
 */
class AdminSetPermission extends Api
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
            'role_uuid'
        ]);
        $this->check($request, "Admin.setPermission");
        $request['uuid'] = $id;
        $result = AdminLogic::setPermission($request,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }


}
