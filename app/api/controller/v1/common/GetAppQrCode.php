<?php

namespace app\api\controller\v1\common;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\common\GetAppQrCodeLogic;

/**
 * 获取app二维码
 * User: Yacon
 * Date: 2022-04-17
 * Time: 09:59
 */
class GetAppQrCode extends Api
{
  public $restMethodList = 'get|post|put|delete';

  public function index()
  {
    $request = $this->selectParam([
      'path' => '', // 扫码进入的小程序页面路径，最大长度 128 字节，不能为空
      'width' => 430, // 二维码的宽度，单位 px。最小 280px，最大 1280px
      'auto_color' => false, // 默认值false；自动配置线条颜色，如果颜色依然是黑色，则说明不建议配置主色调
      'is_hyaline' => false, // 默认值false；是否需要透明底色，为 true 时，生成透明底色的小程序码
    ]);
    if (!$request['path']) {
      throw new Exception('请提供扫码后跳转地址');
    }
    $result = GetAppQrCodeLogic::commonList($request, $this->userInfo);

    $this->render(200, ['result' => $result]);
  }
}
