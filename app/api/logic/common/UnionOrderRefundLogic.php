<?php

namespace app\api\logic\common;

use app\api\model\GoodsOrder;
use app\api\model\IntegralOrder;
use app\api\model\IntegralRecode;
use app\api\model\JournalAccount;
use app\api\model\Order;
use app\api\model\OrderGoods;
use app\api\model\PackageOrder;
use app\api\model\User;
use app\common\wechat\Pay;
use app\common\wechat\PayV3;
use phpDocumentor\Reflection\DocBlock\Tags\Example;
use think\Config;
use think\Exception;
use think\Db;

/**
 * 统一下单-退款
 * User: Yacon
 * Date: 2022-03-08
 * Time: 17:31
 */
class UnionOrderRefundLogic
{
  static public function commonAdd($request, $userInfo)
  {
    try {
      $method = [
        'integral' => 'integralOrder', // 积分订单-取消订单-退还运费
        'distribution' => 'distributionOrder' // 分销订单-取消订单-退还所有金额
      ];
      $order = call_user_func_array([self::class, $method[$request['type']]], [$request, $userInfo]);

      $wechat_config  = Config::get('wechat');
      if ($request['port'] == 'user') {
        $MinAppID = $wechat_config['MinAppID'];
        $MchId = $wechat_config['MinMchId'];
        $MchKey = $wechat_config['MinMchKey'];
        $MinMchSerial = $wechat_config['MinMchSerial'];
      }
      $pay = new PayV3($MinAppID, $MchId, $MchKey, $MinMchSerial);

      $result = $pay->refund($order['refund_uuid'], $order['pay_no'], $order['total_price'], $order['refund_total']);

      return $result;
    } catch (Exception $e) {
      throw new Exception($e->getMessage(), 500);
    }
  }

  // 积分订单
  public static function integralOrder($request, $userInfo)
  {
    // 更新订单信息
    $order = IntegralOrder::build()->where(['uuid' => $request['order_uuid']])->find();
    if ($order['freight_pay'] != 2) {
      throw new Exception('无法退还运费');
    }

    $result['total_price'] = $order['freight'];
    $result['pay_no'] = $order['pay_id'];
    $result['refund_total'] = $order['freight'];
    $result['refund_uuid'] = uuid();

    // 记录流水账
    $number = JournalAccount::build()->createID();
    JournalAccount::build()->insert([
      'uuid' => uuid(),
      'create_time' => now_time(time()),
      'update_time' => now_time(time()),
      'serial_number' => $number[0],
      'payment_number' => $number[1],
      'payment_type' => 2,
      'journal_type' => 1,
      'price' => $order['freight'],
      'user_uuid' => $order['user_uuid'],
      'order_uuid' => $order['uuid'],
    ]);

    return $result;
  }

  // 分销订单
  public static function distributionOrder($request, $userInfo)
  {
    // 更新订单信息
    $order = Order::build()->where(['uuid' => $request['order_uuid']])->find();
    $result['total_price'] = $order['pay_price'];
    $result['pay_no'] = $order['pay_id'];
    $result['refund_total'] = $request['price'];
    $result['refund_uuid'] = uuid();

    // 记录流水账
    $number = JournalAccount::build()->createID();
    JournalAccount::build()->insert([
      'uuid' => uuid(),
      'create_time' => now_time(time()),
      'update_time' => now_time(time()),
      'serial_number' => $number[0],
      'payment_number' => $number[1],
      'payment_type' => 2,
      'journal_type' => 2,
      'price' => $request['price'],
      'user_uuid' => $order['user_uuid'],
      'order_uuid' => $order['uuid'],
    ]);
    return $result;
  }
}
