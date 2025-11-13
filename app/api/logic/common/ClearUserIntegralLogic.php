<?php

/**
 * Author Yacon
 * Date 2022/02/15 16:45
 */

namespace app\api\logic\common;

use app\api\model\ActivitiesPhoto;
use app\api\model\OrderSetting;
use app\api\model\User;

class ClearUserIntegralLogic
{

    public static function sync()
    {

        $now = date('y-m-d', time());
        // 更新拍照活动状态为开始状态
        ActivitiesPhoto::build()->where(['begin_time' => ['<=', $now], 'is_deleted' => 1])->update(['is_stop' => 2]);
        // 更新拍照活动状态为结束状态
        ActivitiesPhoto::build()->where(['end_time' => ['<=', $now], 'is_deleted' => 1])->update(['is_stop' => 3]);
        // 更新拍照活动状态为隐藏状态
        ActivitiesPhoto::build()->where(['hide_time' => ['<=', $now], 'is_deleted' => 1])->update(['state' => 2]);


        // 查询用户积分清零时间
        $clearDate = OrderSetting::build()->where(['uuid' => '33196d4808444284b7e78e72693c3b4b'])->value('clear_integral');

        $now = date('Y-m-d', time());

        // 清空用户积分
        if ($now >= $clearDate) {
            User::build()->where(['is_deleted' => 1])->update(['integral' => 0]);
        }
    }
}
