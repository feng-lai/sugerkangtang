<?php

namespace app\api\controller\v1\common;

use app\api\controller\Api;
use app\api\logic\common\AdminLoginLogic;
use think\Exception;

/**
 * 登录-控制器
 */
class AdminLogin extends Api
{
    public $restMethodList = 'post';

    public function save()
    {
        $request = $this->selectParam([
            "password",
            "uname",
            "captcha",
            "captcha_id",
        ]);
        $this->check($request, "AdminLogin.save");
        $result = AdminLoginLogic::cmsAdd($request);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
