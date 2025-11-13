<?php

namespace app\api\controller\v1\common;

use app\api\controller\Api;
use app\api\controller\Sms;
use app\api\model\Captcha;
use think\Exception;

class SendSmsCode extends Api
{
    public $restMethodList = 'post|put';

    /**
     * 获取手机验证码
     */
    public function save()
    {
        $request = $this->selectParam(['mobile']);
        $this->check($request, "SendSmsCode.captcha");
        $smsObj = new Sms();
        $res = $smsObj->send_notice($request['mobile']);
        if ($res) {
            $this->render(200, ['result' => true]);
        }
        $this->returnmsg(403, $data = [], $header = [], $type = "", "Server error", $message = "验证码发送失败");
    }

    /**
     * 验证码校验
     */
    public function update($id)
    {
        $request = $this->selectParam([
          'code',
        ]);
        if(!$request['code']){
          throw new Exception('验证码不能为空');
        }
        $request['mobile'] = $id;
        Captcha::build()->captchaCheck($request);
        $this->render(200, ['result' => true]);
    }
}
