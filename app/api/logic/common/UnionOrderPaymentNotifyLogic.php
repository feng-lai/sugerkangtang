<?php

namespace app\api\logic\common;

use app\api\model\Goods;
use app\api\model\GoodsOrder;
use app\api\model\IntegralGoods;
use app\api\model\IntegralOrder;
use app\api\model\IntegralRecode;
use app\api\model\InterestOrder;
use app\api\model\JournalAccount;
use app\api\model\Message;
use app\api\model\Order;
use app\api\model\OrderGoods;
use app\api\model\PackageOrder;
use app\api\model\User;
use app\common\wechat\Pay;
use app\common\wechat\PayV3;
use app\common\wechat\Util;
use think\Cache;
use think\Config;
use think\Exception;
use think\Db;

/**
 * 统一下单-回调-逻辑
 * User: Yacon
 * Date: 2022-03-07
 * Time: 17:43
 */
class UnionOrderPaymentNotifyLogic
{
  static public function commonAdd($request, $headers)
  {
    try {
      Db::startTrans();

      $wechat_config  = Config::get('wechat');
      $MinAppID = $wechat_config['MinAppID'];
      $MchId = $wechat_config['MinMchId'];
      $MchKey = $wechat_config['MinMchKey'];
      $MinMchSerial = $wechat_config['MinMchSerial'];
      $pay = new PayV3($MinAppID, $MchId, $MchKey, $MinMchSerial);
      $result = $pay->handleNotify($request, $headers);


      // 支付失败
      if (!$result) {
        return [
          'code' => 'FAIL',
          'message' => '失败'
        ];
      }

      $result['attach'] = json_decode($result['attach'], true);


      $method = [
        'integral' => 'integralOrder',
        'distribution' => 'distributionOrder',
        'interest' => 'interestOrder',
      ];
      call_user_func_array([self::class, $method[$result['attach']['order_type']]], [$result]);

      Db::commit();
      return [
        'code' => 'SUCCESS'
      ];
    } catch (Exception $e) {
      Db::rollback();
      file_put_contents('error.txt', json_encode($e->getMessage()));
      throw new Exception($e->getMessage(), 500);
    }
  }

  // 积分订单
  public static function integralOrder($result)
  {
    // 查询订单信息
    $order = IntegralOrder::build()->where(['uuid' => $result['attach']['order_uuid']])->find();

    // 查询是否存在该订单的流水账，如果不存在，则记录
    if (!JournalAccount::build()->where(['order_uuid' => $order['uuid'], 'transaction_id' => $order['transaction_id']])->count()) {
      // 更新订单信息
      $order['transaction_id'] = $result['transaction_id'];
      $order['update_time'] = now_time(time());
      $order['pay_time'] = now_time(time());
      $order['freight_pay'] = 2;
      $order['state'] = 1;
      $order->save();

      // 记录流水账
      $number = JournalAccount::build()->createID();
      JournalAccount::build()->insert([
        'uuid' => uuid(),
        'create_time' => now_time(time()),
        'update_time' => now_time(time()),
        'serial_number' => $number[0],
        'payment_number' => $number[1],
        'price' =>  $order['freight'],
        'payment_type' => 1,
        'journal_type' => 1,
        'transaction_id' => $result['transaction_id'],
        'order_uuid' => $order['uuid'],
        'user_uuid' => $order['user_uuid'],
      ]);

      // 消息发送
      $goods = IntegralGoods::build()->where(['uuid' => $order['integral_goods_uuid']])->find();
      Message::build()->add($order['user_uuid'], '积分商品兑换通知', "兑换成功，您已使用{$goods['integral']}积分成功兑换到{$goods['title']}商品，我们将尽快为您发货", 2);
    }
  }

  // 分销订单
  public static function distributionOrder($result)
  {
    // 查询订单信息
    $order = Order::build()->where(['uuid' => $result['attach']['order_uuid']])->find();
    // 查询是否存在该订单的流水账，如果不存在，则记录
    if (!JournalAccount::build()->where(['order_uuid' => $order['uuid'], 'transaction_id' => $order['transaction_id']])->count()) {

      // 更新订单信息
      $order['transaction_id'] = $result['transaction_id'];
      $order['update_time'] = now_time(time());
      $order['pay_time'] = now_time(time());
      $order['state'] = 2;
      $order->save();

      // 记录流水账
      $number = JournalAccount::build()->createID();
      JournalAccount::build()->insert([
        'uuid' => uuid(),
        'create_time' => now_time(time()),
        'update_time' => now_time(time()),
        'serial_number' => $number[0],
        'payment_number' => $number[1],
        'price' =>  $order['pay_price'],
        'payment_type' => 1,
        'journal_type' => 2,
        'transaction_id' => $order['transaction_id'],
        'order_uuid' => $order['uuid'],
        'user_uuid' => $order['user_uuid'],
      ]);


      // 更新用户积分
      User::build()->where(['uuid' => $order['user_uuid']])->setInc('integral', $order['integral']);
      // 更新用户订单数量
      User::build()->where(['uuid' => $order['user_uuid']])->setInc('order_num');

      // 写入积分记录
      IntegralRecode::build()->insert([
        'uuid' => uuid(),
        'create_time' => now_time(time()),
        'update_time' => now_time(time()),
        'user_uuid' => $order['user_uuid'],
        'type' => 1,
        'integral' => $order['integral'],
        'content' => '购买商品',
        'integral_order_uuid' => $order['uuid']
      ]);

      // 更新商品库存及冻结库存
      $orderGoods = OrderGoods::build()->where(['order_uuid' => $order['uuid']])->select();
      $orderGoods = objToArray($orderGoods);
      foreach ($orderGoods as $v) {
        // 减少库存
        Goods::build()->where(['uuid' => $v['uuid']])->setDec('stock', $v['number']);
        // 减少冻结库存
        Goods::build()->where(['uuid' => $v['uuid']])->setDec('stock_frozen', $v['number']);
      }
    }
  }

  // 权益订单
  public static function interestOrder($result)
  {
    // 查询订单信息
    $order = InterestOrder::build()->where(['uuid' => $result['attach']['order_uuid']])->find();

    // 查询是否存在该订单的流水账，如果不存在，则记录
    if (!JournalAccount::build()->where(['order_uuid' => $order['uuid'], 'transaction_id' => $order['transaction_id']])->count()) {
      // 更新订单信息
      $order['transaction_id'] = $result['transaction_id'];
      $order['update_time'] = now_time(time());
      $order['pay_time'] = now_time(time());
      $order['freight_pay'] = 2;
      $order['state'] = 1;
      $order->save();

      // 记录流水账
      $number = JournalAccount::build()->createID();
      JournalAccount::build()->insert([
        'uuid' => uuid(),
        'create_time' => now_time(time()),
        'update_time' => now_time(time()),
        'serial_number' => $number[0],
        'payment_number' => $number[1],
        'price' =>  $order['freight'],
        'payment_type' => 1,
        'journal_type' => 1,
        'transaction_id' => $result['transaction_id'],
        'order_uuid' => $order['uuid'],
        'user_uuid' => $order['user_uuid'],
      ]);
    }
  }
}
