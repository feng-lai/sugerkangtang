<?php

namespace app\api\controller\v1\common;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\common\UnionOrderPaymentNotifyLogic;

/**
 * 统一下单-回调-控制器
 * User: Yacon
 * Date: 2022-03-07
 * Time: 17:43
 */
class UnionOrderPaymentNotify extends Api
{
  public $restMethodList = 'post';

  public function save()
  {

    $params = file_get_contents('php://input');
    $headers = getallheaders();

    $result = UnionOrderPaymentNotifyLogic::commonAdd($params, $headers);

    if ($result['code'] == 'FAIL') {
      http_response_code(500);
      $result = json_encode($result);
    } else {
      http_response_code(200);
    }
    header("Content-type: application/json");
    session_start();
    echo $result;
    ob_flush();
    flush();
    session_destroy();
  }
}
