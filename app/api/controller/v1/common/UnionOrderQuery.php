<?php

namespace app\api\controller\v1\common;

use app\api\controller\Api;
use app\api\logic\common\UnionOrderQueryLogic;
use Exception;

/**
 * 统一下单-订单查询-控制器
 * User: Yacon
 * Date: 2022-03-08
 * Time: 17:12
 */
class UnionOrderQuery extends Api
{
  public $restMethodList = 'post';

  public function save()
  {
    $request = $this->selectParam([
      'order_uuid' => '', // 订单uuid
      'type' => '', // 订单类型 integral=积分订单 distribution=分销订单
      'port' => 'user', // 支付终端 user=用户端
    ]);
    if (!$request['order_uuid']) {
      throw new Exception('订单不能为空', 400);
    }
    if (!$request['type']) {
      throw new Exception('订单类型不能为空', 400);
    }

    if ($request['port'] == 'user') {
      $this->userInfo = $this->miniValidateToken();
    }

    $result = UnionOrderQueryLogic::commonAdd($request, $this->userInfo);
    if (isset($result['msg'])) {
      $this->returnmsg(400, [], [], '', '', $result['msg']);
    } else {
      $this->render(200, ['result' => $result]);
    }
  }
}
