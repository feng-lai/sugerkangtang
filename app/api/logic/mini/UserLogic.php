<?php

namespace app\api\logic\mini;

use app\api\model\Captcha;
use app\api\model\Cart;
use app\api\model\Order;
use app\api\model\Partner;
use app\api\model\Retail;
use app\api\model\User;
use app\api\model\Message;
use think\Exception;

/**
 * 用户信息-逻辑
 */
class UserLogic
{
    static public function miniList($userInfo)
    {
        // 用户信息
        $result = User::build()->field('
            uuid,
            name,
            img,
            gender,
            phone,
            height,
            weight,
            birthday,
            invite_uuid,
            pendding_order_msg,
            cancel_order_msg,
            ship_order_msg,
            report_review_msg,
            report_expire_msg,
            after_sale_msg
        ')->where('uuid', $userInfo['uuid'])
            ->find();
        $result->cart_num = Cart::build()->where('user_uuid', $userInfo['uuid'])->where('is_deleted',1)->count();
        $is_retail = Retail::build()->where('user_uuid', $userInfo['uuid'])->where('is_deleted',1)->count();
        if($is_retail > 0){
            $result->is_retail = 1;
        }else{
            $result->is_retail = 2;
        }
        $is_partner = Partner::build()->where('user_uuid', $userInfo['uuid'])->where('is_deleted',1)->count();
        if($is_partner > 0){
            $result->is_partner = 1;
        }else{
            $result->is_partner = 2;
        }
        return $result;
    }

    static public function changePhone($request,$userInfo){
        try {
            Captcha::build()->captchaCheck(['mobile' => $request['phone'], 'code' => $request['code']]);
            User::build()->where('uuid', $userInfo['uuid'])->update(['phone' => $request['phone']]);
            return true;
        }catch (\Exception $e){
            throw new Exception($e->getMessage(), 500);
        }

    }
    static public function miniSave($request,$userInfo){
        try {
            $retail = Retail::build()->where('user_uuid', $userInfo['uuid'])->find();
            if($retail){
                $retail->save(['name'=>$request['name']]);
            }
            User::build()->where('uuid', $userInfo['uuid'])->update(array_filter($request));
            return true;
        }catch (\Exception $e){
            throw new Exception($e->getMessage(), 500);
        }
    }
}
