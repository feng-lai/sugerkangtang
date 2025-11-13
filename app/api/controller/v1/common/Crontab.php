<?php

namespace app\api\controller\v1\common;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use app\api\controller\Api;
use app\api\logic\common\UserToRetailLogic;
use app\api\logic\common\CancelOrderLogic;
use app\api\logic\common\AutoConfirmOrderLogic;
use app\api\logic\common\PartnerOrderAutoSettlementLogic;
use app\api\logic\common\AutoSettlementLogic;
use app\api\logic\common\UserToPartnerLogic;
use Exception;

class Crontab extends Api
{

    /**
     * 允许访问的方式列表，资源数组如果没有对应的方式列表，请不要把该方法写上，如user这个资源，客户端没有delete操作
     */
    public $restMethodList = 'get|post|options';

    /**
     * @param string $type
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     * @throws \think\Exception
     */
    public function index($type = "")
    {
        switch ($type) {
            case 'UserToRetail':
                // 用户自动成为推广员
                // /v1/common/Crontab?type=UserToRetail
                // 每30秒执行一次
                UserToRetailLogic::sync();
                break;
            case 'CancelOrder':
                // 自动取消未支付订单
                // /v1/common/Crontab?type=CancelOrder
                // 每30秒执行一次
                CancelOrderLogic::sync();
                break;
            case 'AutoConfirmOrder':
                // 自动确认收货
                // /v1/common/Crontab?type=AutoConfirmOrder
                // 每30秒执行一次
                AutoConfirmOrderLogic::sync();
                break;

            case 'AutoSettlement':
                // 允许售后的订单售后期到了自动结算
                // /v1/common/Crontab?type=AutoSettlement
                // 每30秒执行一次
                AutoSettlementLogic::sync();
                break;

            case 'UserToPartner':
                // 用户符合条件自动申请高级合伙人
                // /v1/common/Crontab?type=UserToPartner
                // 每30秒执行一次
                UserToPartnerLogic::sync();
                break;

            case 'PartnerOrderAutoSettlement':
                // 2+1分销间推订单自动结算
                // /v1/common/Crontab?type=PartnerOrderAutoSettlement
                // 每30秒执行一次
                PartnerOrderAutoSettlementLogic::sync();
                break;
        }
    }

    function getCurl($url)
    {
        try {
            $curlHandle = curl_init();
            curl_setopt($curlHandle, CURLOPT_URL, $url);
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, false);
            $result = curl_exec($curlHandle);
            curl_close($curlHandle);
            return $result;
        } catch (Exception $e) {
            return null;
        }
    }
}
