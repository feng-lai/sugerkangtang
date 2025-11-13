<?php
namespace app\common\tools;

use app\api\model\Refund;
use app\common\wechat\Util;
use app\api\controller\Api;
class WechatRefund{
    //微信退款

    private function getWeChatRefundData($out_refund_no, $outTradeNo, $total_fee, $fee)
    {

        $orderData = [
            'appid' => config('wechat.MinAppID'), // 公众账号ID
            'mch_id' => config('wechat.MinMchId'), // 商户号
            'nonce_str' => Util::getRandomString(32), // 随机字符串
            'sign' => '',
            'sign_type' => 'MD5',
            'out_trade_no' => $outTradeNo, // 商户系统内部订单号
            'out_refund_no' => $out_refund_no, // 商户系统内部退款单号
            'fee_type' => 'CNY',
            'total_fee' => $total_fee,//订单金额
            'refund_fee' => $fee,//退款金额
            'refund_account' => "REFUND_SOURCE_RECHARGE_FUNDS",//退款资金来源 默认为未结算资金 当前设置为余额资金
        ];
        $orderData['sign'] = Util::makeSign($orderData, config('wechat.MinMchKey'));
        $result = Util::toXml($orderData);
        return $result;
    }

    /**
     * @author: Jason
     * @time: 2019年8月11日
     * description:退款
     * @param string $outRefundNo 退款单号
     * @param string $outTradeNo 支付单号
     * @param float $total_fee 支付订单总金额
     * @param float $fee 本次退款金额
     * @param float $refund_content 退款备注
     * @param string $type 退款类型
     * @return bool
     */
    public function refund($outRefundNo, $outTradeNo, $total_fee, $fee,$refund_content)
    {
        $xmlData = $this->getWeChatRefundData($outRefundNo, $outTradeNo, $total_fee * 100, abs($fee) * 100);
//        var_dump($xmlData);die;
        $result = Util::curl_post_ssl("https://api.mch.weixin.qq.com/secapi/pay/refund", $xmlData);

        if (empty($result)) {
            return false;
        }
        $result = Util::xmlParser($result);
        if (empty($result)) {
            return false;
        }
        $refund['uuid'] = uuid();
        $refund['response']=json_encode($result);
        $refund['request']=$xmlData;
        $refund['trade_no']=$outRefundNo;
        $refund['order_id']=$outTradeNo;
        $refund['price']=$fee;
        $refund['content']=$refund_content;
        $refund['create_time']=now_time(time());
        Refund::build()->insert($refund);
        if ($result['return_code'] != 'SUCCESS') {
            return false;
        }
        if ($result['return_code'] != 'SUCCESS' || $result['result_code'] != 'SUCCESS') {
            log_file($result['err_code_des'],"wechatRefund","wechatRefund");
            return false;
        }
        return true;
    }
}