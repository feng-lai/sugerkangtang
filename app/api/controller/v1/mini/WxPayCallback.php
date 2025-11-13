<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use think\Request;
use app\api\logic\mini\WxPayCallbackLogic;

/**
 * 微信支付回调-控制器
 * User:
 * Date:
 * Time:
 */
class WxPayCallback extends Api
{
  public $restMethodList = 'post';

  public function save()
  {
    $request = file_get_contents("php://input");
    $result = WxPayCallbackLogic::miniAdd($request);
    return array2xml(['return_code'=>$result]);
  }

}
