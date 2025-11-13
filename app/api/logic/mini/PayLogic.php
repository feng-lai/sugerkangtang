<?php

namespace app\api\logic\mini;

use app\api\model\Order;
use app\api\model\Bill;
use app\api\model\RechangeSet;
use app\api\model\ContestantGift;
use app\api\model\GiftSet;
use app\api\model\User;
use think\Config;
use think\Exception;
use think\Db;
use Alipay\aop\AopClient;
use Alipay\aop\request\AlipayTradeAppPayRequest;
use Alipay\aop\request\AlipayTradeWapPayRequest;
use app\common\tools\wechatPay;
use app\common\tools\IosPay;

/**
 * 支付-逻辑
 * User:
 * Date: 2022-07-21
 * Time: 14:31
 */
class PayLogic
{

    /**
     * Author: Administrator
     */
    static public function save($request,$userInfo)
    {
        $order = Order::build()->where('order_id', $request['order_id'])->findOrFail();
        if ($order->status != 1) {
            return ['msg' => '订单不是待支付状态'];
        }
        return wechatPay::store($order->price, $order->order_id,$userInfo['openid']);
    }

}
