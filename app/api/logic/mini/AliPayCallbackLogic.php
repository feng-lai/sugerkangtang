<?php

namespace app\api\logic\mini;

use app\api\model\Captcha;
use app\api\model\Interest;
use app\api\model\InterestBirthday;
use app\api\model\Level;
use app\api\model\Message;
use app\api\model\Order;
use app\api\model\Contestant;
use app\api\model\Agree;
use app\api\model\UserInterrest;
use app\api\model\UserToken;
use think\Exception;
use think\Db;

/**
 * 支付宝回调-逻辑
 * User: Yacon
 * Date: 2022-07-21
 * Time: 14:31
 */
class AliPayCallbackLogic
{
  static public function miniAdd($request)
  {
    try {
      if ($request['trade_status'] == 'TRADE_SUCCESS') {
        $order_sn = $request['out_trade_no'];
        $order = Order::build();
        $data = $order->where('order_sn', $order_sn)->find();
        if ($data['status'] == 1) {
          return "success";
        }
        $save_data = ['status' => 1, 'trade_no' => $request['trade_no'], 'pay_time' => date('Y-m-d H:i:s'), 'pay_type'=>3];
        if ($order->where('order_sn', $order_sn)->update($save_data) !== false) {
          $order->pay_success($data);
          return "success";
        }
      }
      return true;

    } catch (Exception $e) {
      throw new Exception($e->getMessage(), 500);
    }
  }

  // static public function miniEdit($request, $userInfo)
  // {
  //   try {
  //     Db::startTrans();
  //     $user = User::build()->where('uuid', $request['uuid'])->find();
  //     $user['update_time'] = now_time(time());
  //     $user->save();
  //     Db::commit();
  //     return true;
  //   } catch (Exception $e) {
  //     Db::rollback();
  //     throw new Exception($e->getMessage(), 500);
  //   }
  // }

  // static public function miniDelete($id, $userInfo)
  // {
  //   try {
  //     Db::startTrans();
  //     User::build()->where('uuid', $id)->update(['is_deleted' => 2]);
  //     Db::commit();
  //     return true;
  //   } catch (Exception $e) {
  //     Db::rollback();
  //     throw new Exception($e->getMessage(), 500);
  //   }
  // }
}
