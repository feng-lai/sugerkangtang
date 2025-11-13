<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\AdminLogic;

/**
 * 后台用户重置密码-控制器
 */
class AdminResetPassword extends Api
{
    public $restMethodList = 'post';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function save()
    {
        $request = $this->selectParam([
            'admin_uuid',
            'reset_password_note'
        ]);
        $this->check($request, "Admin.resetPassword");
        $result = AdminLogic::resetPassword($request,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }


}
