<?php

namespace app\api\controller\v1\common;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\common\FetchUserPhoneLogic;

/**
 * 获取用户手机号-控制器
 * User: Yacon
 * Date: 2022-02-15
 * Time: 10:25
 */
class FetchUserPhone extends Api
{
  public $restMethodList = 'get|post|put|delete';

  public function save()
  {
    $request = $this->selectParam([
      'type' => 'user', // 终端类型 user=用户端 admin=管家端
      'code' => '', // 手机号获取凭证 v=1有效
      'iv', // 加密算法的初始向量 v=2有效
      'encryptedData', // 包括敏感数据在内的完整用户信息的加密数据 v=2有效
      'v' => 1, // 版本号 1=旧版本 2=新版本
      'session_key', // type=admin&v=1时必填
    ]);
    if ($request['v'] == 1 && !$request['encryptedData']) {
      throw new Exception('加密数据不能为空', 400);
    }
    if ($request['v'] == 1 && !$request['iv']) {
      throw new Exception('初始向量不能为空', 400);
    }
    if ($request['v'] == 2 && !$request['code']) {
      throw new Exception('凭证不能为空', 400);
    }
    if ($request['v'] == 1 && $request['type'] == 'user') {
      $result = FetchUserPhoneLogic::commonAdd($request);
    } else if ($request['v'] == 1 && $request['type'] == 'admin') {
      if (!$request['session_key']) {
        throw new Exception('请先进行微信授权', 400);
      }
      $result = FetchUserPhoneLogic::commonAdd($request, null, $request['session_key']);
    } else {
      $result = FetchUserPhoneLogic::commonAdd($request);
    }
    if (isset($result['msg'])) {
      $this->returnmsg(400, [], [], '', '', $result['msg']);
    } else {
      $this->render(200, ['result' => $result]);
    }
  }
}
