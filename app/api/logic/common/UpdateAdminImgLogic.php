<?php

/**
 * Author Yacon
 * Date 2022/02/15 16:45
 */

namespace app\api\logic\common;

use app\api\model\Admin;

class UpdateAdminImgLogic
{

    public static function sync()
    {
        //生成头像
        $user = Admin::build()->field('uuid,name')->whereNull('img')->whereNotNull('name')->limit(1000)->select();
        $save_user_data = [];
        foreach($user as $v){
            $lastTwo = mb_substr($v->name, -2, 2, 'UTF-8');
            $secondLast = mb_substr($lastTwo, 0, 1, 'UTF-8');
            $last = mb_substr($lastTwo, 1, 1, 'UTF-8');
            $img = generateAvatar($secondLast,$last);
            $save_user_data[] = [
                'uuid' => $v->uuid,
                'img' => $img,
            ];
        }
        Admin::build()->saveAll($save_user_data);
    }
}
