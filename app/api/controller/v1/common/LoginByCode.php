<?php

namespace app\api\controller\v1\common;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\common\loginByCodeLogic;

/**
 * 登录-控制器
 * User: Yacon
 * Date: 2022-02-15
 * Time: 10:36
 */
class LoginByCode extends Api
{
    public $restMethodList = 'get|post|put|delete';

    public function save()
    {
        $request = $this->selectParam([
            'wx_code',
            'code',
            'phone',
            'user_uuid',
            'site_id'=>1
        ]);
        $this->check($request, "loginByCode.save");
        $result = loginByCodeLogic::loginByCode($request);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
