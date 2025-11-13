<?php

namespace app\api\logic\mini;

use app\api\model\Config;
use app\api\model\Order;
use app\api\model\OrderDetail;
use think\Db;
use think\Exception;

/**
 * 微信支付回调-逻辑
 * User:
 * Date:
 * Time:
 */
class WxPayCallbackLogic
{
    static public function miniAdd($request)
    {
        try {
            Db::startTrans();
            $request = xml2array($request);
            if ($request['result_code'] == 'SUCCESS' && $request['return_code'] == 'SUCCESS') {
                $order_id = $request['out_trade_no'];
                $order = Order::build();
                $data = $order->where('order_id', $order_id)->find();
                if ($data['status'] == 2) {
                    return "SUCCESS";
                }
                $save_data = ['status' => 2, 'trade_no' => $request['transaction_id'], 'pay_time' => date('Y-m-d H:i:s'), 'update_time' => date('Y-m-d H:i:s')];
                if ($order->where('order_id', $order_id)->update($save_data) !== false) {
                    $order->pay_success($data);
                    Db::commit();
                    return "SUCCESS";
                }
            }else{
                Order::build()->pendding_msg($request['out_trade_no']);
            }
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
            return "FAIL";
        }
    }
}
