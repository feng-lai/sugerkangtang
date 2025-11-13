<?php

namespace app\api\controller\v1\common;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\common\IosLoginLogic;

/**
 * 苹果登录-控制器
 * User:
 * Date:
 * Time:
 */
class IosLogin extends Api
{
  public $restMethodList = 'get|post|put|delete';

  public function save()
  {
    $request = $this->selectParam([
      'identity_token', // 苹果登录token
      'user_uuid',//邀请者uuid
      'mobile',//手机号
      'v_code',//手机验证码
      'apple_union_id'//苹果用户id
    ]);
    $this->check($request, "IosLogin.save");
    $result = IosLoginLogic::commonAdd($request);
    if (isset($result['msg'])) {
      $this->returnmsg(400, [], [], '', '', $result['msg']);
    } else {
      $this->render(200, ['result' => $result]);
    }
  }
}
