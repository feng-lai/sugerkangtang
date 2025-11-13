<?php
namespace app\common\tools;

use app\api\model\WechatRefund as WechatRefundModel;
use app\common\tools\aop\AopClient;
use app\common\tools\aop\request\AlipayTradeRefundRequest;
use app\common\wechat\Util;
use app\api\controller\Api;
class AliRefund{
    //支付宝退款


    /**
     * @author: jason
     * @time: 2019年10月1、9日
     * description:退款
     * @param string $outRefundNo 退款单号
     * @param string $outTradeNo 支付单号
     * @param float $total_fee 支付订单总金额
     * @param float $fee 本次退款金额
     * @return bool
     */
    public function refund($outRefundNo, $outTradeNo, $total_fee, $fee,$refund_content)
    {
        $aop = new AopClient();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = config('alipay.AppID');
        $aop->rsaPrivateKey = config('alipay.rsaPrivateKey');
        $aop->alipayrsaPublicKey=config('alipay.alipayPublicKey');
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset='utf-8';
        $aop->format='json';
        $request = new AlipayTradeRefundRequest();
        $request->setBizContent("{" .
            "\"out_trade_no\":\"{$outRefundNo}\"," .
            "\"trade_no\":\"{$outTradeNo}\"," .
            "\"refund_amount\":{$fee}," .
            "\"refund_reason\":\"订单取消退款\"" .
            "  }");
//        var_dump($request);
        $result = $aop->execute($request);
        $result=json_decode(json_encode($result),true);

        $refund['response']=json_encode($result);
        $refund['order_no']=$outTradeNo;
        $refund['content']=$refund_content;
        $refund['type']="1"; //1支付宝 2微信
        $refund['create_time']=now_time(time());
        WechatRefundModel::build()->insert($refund);

        if(!empty($result['alipay_trade_refund_response'])){
            if($result['alipay_trade_refund_response']['code']=="10000"){
                return true;

            }else{
                //退款失败
                $error['msg']=$result['alipay_trade_refund_response']['sub_msg'];
                return $error;
            }
        }else{
          //退款失败
            $error['msg']=$result;
            return $error;

        }

    }
}