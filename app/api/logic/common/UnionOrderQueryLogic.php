<?php

namespace app\api\logic\common;

use app\api\model\GoodsOrder;
use app\api\model\IntegralOrder;
use app\api\model\JournalAccount;
use app\api\model\Order;
use app\api\model\PackageOrder;
use app\api\model\UnionOrderQuery;
use app\common\wechat\Pay;
use app\common\wechat\PayV3;
use think\Config;
use think\Exception;
use think\Db;

/**
 * 统一下单-订单查询-逻辑
 * User: Yacon
 * Date: 2022-03-08
 * Time: 17:12
 */
class UnionOrderQueryLogic
{
  static public function commonAdd($request, $userInfo)
  {
    try {

      $method = [
        'integral' => 'integralOrder',
        'distribution' => 'distributionOrder',
      ];
      $out_trade_no = call_user_func_array([self::class, $method[$request['type']]], [$request, $userInfo]);

      $wechat_config  = Config::get('wechat');
      if ($request['port'] == 'user') {
        $MinAppID = $wechat_config['MinAppID'];
        $MchId = $wechat_config['MinMchId'];
        $MchKey = $wechat_config['MinMchKey'];
        $MinMchSerial = $wechat_config['MinMchSerial'];
      }
      $pay = new PayV3($MinAppID, $MchId, $MchKey, $MinMchSerial);

      $result = $pay->query($out_trade_no);

      return $result;
    } catch (Exception $e) {
      throw new Exception($e->getMessage(), 500);
    }
  }

  // 积分订单
  public static function integralOrder($request, $userInfo)
  {
    $pay_id = IntegralOrder::build()->where(['uuid' => $request['order_uuid']])->value('pay_id');
    if (!$pay_id) {
      throw new Exception('该订单未支付', 500);
    }
    return $pay_id;
  }

  // 分销订单
  public static function distributionOrder($request, $userInfo)
  {
    $pay_id = Order::build()->where(['uuid' => $request['order_uuid']])->value('pay_id');
    if (!$pay_id) {
      throw new Exception('该订单未支付', 500);
    }
    return $pay_id;
  }
}
