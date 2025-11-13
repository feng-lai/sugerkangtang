<?php

namespace app\api\controller\v1\common;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\common\loginByPasswordLogic;

/**
 * 登录-控制器
 */
class LoginByPassword extends Api
{
  public $restMethodList = 'get|post|put|delete';

  public function save()
  {
    $request = $this->selectParam([
        "password",
        "user_name",
    ]);

    $this->check($request, "loginByPassword.save");
    $result = loginByPasswordLogic::loginByPassword($request);
    if (isset($result['msg'])) {
      $this->returnmsg(400, [], [], '', '', $result['msg']);
    } else {
      $this->render(200, ['result' => $result]);
    }
  }
}
