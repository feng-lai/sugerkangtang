<?php

namespace app\api\controller\v1\common;

use app\api\controller\Api;
use app\api\logic\common\AdminLoginLogic;
use think\Exception;

/**
 * 验证码-控制器
 */
class Captcha extends Api
{
    public $restMethodList = 'get';

    public function read($id)
    {
        return captcha($id);
    }
}
