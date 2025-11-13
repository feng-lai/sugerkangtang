<?php
/**
 * Created by Terry.
 * User: Terry
 * Email: terr_exchange@outlook.com
 * Date: 2020/9/17
 * Time: 17:40
 */

namespace app\api\behavior;


use app\api\logic\app\PaymentLogic;
use app\api\logic\app\PurchaserDepositLogic;
use app\api\logic\mini\SendUniformMessage;
use app\api\model\AppUser;
use app\api\model\AuctionActivityCommodityRelation;
use app\api\model\AuctionCommodity;
use app\api\model\AuctionCommodityOffer;
use app\api\model\AuctionConfig;
use app\api\model\AuctionOrder;
use app\api\model\CommodityMain;
use app\api\model\CommodityPreferential;
use app\api\model\CouponsMain;
use app\api\model\FinanceRecord;
use app\api\model\MerchantBondRecord;
use app\api\model\MerchantMain;
use app\api\model\Notice;
use app\api\model\OfflineIdentify;
use app\api\model\OnlineIdentify;
use app\api\model\OrderAfterSale;
use app\api\model\OrderMain;
use app\api\model\OrderSetting;
use app\api\model\PaymentLog;
use app\api\model\PurchaserDeposit;
use app\api\model\UserAuctionNotice;
use app\api\model\UserBalanceRecord;
use app\api\model\UserCoupon;
use app\api\model\UserDealNotice;
use app\api\model\UserMain;
use app\api\model\UserNotice;
use app\api\model\UserOauth;
use app\common\tools\RedisUtil;
use http\Client\Curl\User;
use think\Db;
use think\Exception;
use think\Hook;

class timer
{
    public function run($method, $event)
    {
//        var_dump(1);die;
        $method = [
            'startAuction'=>'_startAuction',//开启拍品

        ];

        if($method[$event] ?? false) call_user_func_array([self::class,$method[$event]],[]);

    }

    private function _startAuction()
    {

    }

    private function _endAuction()
    {



    }

}
