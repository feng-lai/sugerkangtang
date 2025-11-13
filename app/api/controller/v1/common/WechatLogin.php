<?php

namespace app\api\controller\v1\common;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\common\WechatLoginLogic;

/**
 * 微信小程序-控制器
 */
class WechatLogin extends Api
{
    public $restMethodList = 'get|post|put|delete';

    public function save()
    {
        $request = $this->selectParam([
            'code',
            'phone',
            'user_uuid',
            'site_id'=>1
        ]);
        $this->check($request, "WechatLogin.save");
        $result = WechatLoginLogic::commonAdd($request);
        if (isset($result['msg'])) {
            $this->returnmsg(400, '', [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
