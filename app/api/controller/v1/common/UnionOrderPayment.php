<?php

namespace app\api\controller\v1\common;

use app\api\controller\Api;
use app\api\logic\common\UnionOrderPaymentLogic;

/**
 * 统一下单-控制器
 * User: Yacon
 * Date: 2022-02-19
 * Time: 23:41
 */
class UnionOrderPayment extends Api
{
  public $restMethodList = 'post';

  public function save()
  {
    $request = $this->selectParam([
      'order_uuid' => '', // 订单uuid
      'type' => '', // 订单类型 integral=积分订单 distribution=分销订单 interest=权益订单
      'port' => 'user', // 支付终端 user=用户端
    ]);
    $this->check($request, "UnionOrderPayment.save");
    // 用户端
    if ($request['port'] == 'user') {
      $this->userInfo = $this->miniValidateToken();
    }
    $result = UnionOrderPaymentLogic::commonAdd($request, $this->userInfo);
    if (isset($result['msg'])) {
      $this->returnmsg(400, [], [], '', '', $result['msg']);
    } else {
      $this->render(200, ['result' => $result]);
    }
  }
}
