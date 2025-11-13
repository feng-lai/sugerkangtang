<?php

namespace app\api\logic\common;

use app\api\model\IntegralOrder;
use app\api\model\Order;
use app\api\model\User;
use think\Exception;
use think\Config;
use app\common\wechat\PayV3;

/**
 * 统一下单-逻辑
 * User: Yacon
 * Date: 2022-02-19
 * Time: 23:41
 */
class UnionOrderPaymentLogic
{
  static public function commonAdd($request, $userInfo)
  {
    try {

      $method = [
        'integral' => 'integralOrder',
        'distribution' => 'distributionOrder',
        'interest' => 'interestOrder',
      ];
      $result = call_user_func_array([self::class, $method[$request['type']]], [$request, $userInfo]);

      $openid = User::build()->where(['uuid' => $userInfo['uuid']])->value('openid');

      $wechat_config  = Config::get('wechat');
      // 用户端
      if ($request['port'] == 'user') {
        $MinAppID = $wechat_config['MinAppID'];
        $MchId = $wechat_config['MinMchId'];
        $MchKey = $wechat_config['MinMchKey'];
        $MinMchSerial = $wechat_config['MinMchSerial'];
        $WxNotifyUrl = $wechat_config['MinWxNotifyUrl'];
      }

      $pay = new PayV3($MinAppID, $MchId, $MchKey, $MinMchSerial);
      $requestData = $pay->order($result['pay_no'], $result['total_price'], $openid, $result['order_type'], $result['attach'], 2, $WxNotifyUrl);

      return $requestData;
    } catch (Exception $e) {
      throw new Exception($e->getMessage(), 500);
    }
  }

  /**
   * 积分订单
   */
  public static function integralOrder($request, $userInfo)
  {
    $order = IntegralOrder::build()->where(['uuid' => $request['order_uuid']])->find();
    if ($order['freight'] == 0 || $order['freight_pay'] != 1) {
      throw new Exception('该订单无法支付', 400);
    }

    $result['attach'] = json_encode([
      'order_uuid' => $order['uuid'],
      'order_type' => 'integral',
    ]);
    $result['total_price'] = $order['freight'];
    $result['pay_no'] = $order['pay_id'];
    $result['order_type'] = '积分订单';
    return $result;
  }

  /**
   * 分销订单
   */
  public static function distributionOrder($request, $userInfo)
  {
    $order = Order::build()->where(['uuid' => $request['order_uuid']])->find();
    // if ($order['state'] != 1) {
    //   throw new Exception('该订单无法支付', 400);
    // }

    $result['attach'] = json_encode([
      'order_uuid' => $order['uuid'],
      'order_type' => 'distribution',
    ]);
    $result['total_price'] = $order['pay_price'];
    $result['pay_no'] = $order['pay_id'];
    $result['order_type'] = '分销订单';
    return $result;
  }

  /**
   * 权益订单
   */
  public static function interestOrder($request, $userInfo)
  {
    $order = IntegralOrder::build()->where(['uuid' => $request['order_uuid']])->find();
    if ($order['freight_pay'] != 1) {
      throw new Exception('该订单无法支付', 400);
    }

    $result['attach'] = json_encode([
      'order_uuid' => $order['uuid'],
      'order_type' => 'interest',
    ]);
    $result['total_price'] = $order['freight'];
    $result['pay_no'] = $order['pay_id'];
    $result['order_type'] = '权益订单';
    return $result;
  }
}
