<?php

namespace app\api\controller\v1\common;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\common\FetchQRCodeLogic;

/**
 * 获取小程序二维码-控制器
 * User: Yacon
 * Date: 2022-04-17
 * Time: 09:59
 */
class FetchQRCode extends Api
{
  public $restMethodList = 'get|post|put|delete';

  public function index()
  {
    $request = $this->selectParam([
      'path' => '', // 扫码进入的小程序页面路径，最大长度 128 字节，不能为空
      'width' => 430, // 二维码的宽度，单位 px。最小 280px，最大 1280px
    ]);
    if (!$request['path']) {
      throw new Exception('请提供扫码后跳转地址');
    }
    $result = FetchQRCodeLogic::commonList($request, $this->userInfo);

    $this->render(200, ['result' => $result]);
  }
}
