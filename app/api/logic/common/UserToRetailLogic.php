<?php

/**
 * Author Yacon
 * Date 2022/02/15 16:45
 */

namespace app\api\logic\common;

use app\api\model\Config;
use app\api\model\Retail;
use app\api\model\User;

class UserToRetailLogic
{

    public static function sync()
    {
        $time = Config::build()->where('key', 'AutoRetail')->value('value');
        //查询不是推广员的用户
        $data = User::build()
            ->field('u.uuid,o.status,o.order_id,o.confirm_time,u.invite_uuid,u.site_id')
            ->alias('u')
            ->join('retail r', 'r.user_uuid = u.uuid and r.site_id = u.site_id','left')
            ->join('order o', 'o.user_uuid = u.uuid and o.site_id = u.site_id','left')
            ->distinct(true)
            ->whereNull('r.uuid')
            ->where('o.status',4)
            ->select();
        foreach ($data as $v) {
            if(!Retail::build()->where('user_uuid', $v['uuid'])->count()){
                if(time() - strtotime($v['confirm_time']) > $time*3600){
                    $uuid = uuid();
                    $res = [
                        'uuid' => $uuid,
                        'user_uuid'=>$v['uuid'],
                        'ppuuid'=>$uuid,
                        'name'=>'TKT'.getNumberOne(6),
                        'site_id'=>$v['site_id'],
                        'create_time'=>now_time(time()),
                        'update_time'=>now_time(time()),
                    ];
                    if($v->invite_uuid){
                        $retail = Retail::where(['user_uuid' => $v['invite_uuid']])->find();
                        if($retail){
                            $res['ppuuid'] = $retail->ppuuid;
                            $res['puuid'] = $retail->uuid;
                            $res['level'] = $retail->level+1;
                        }
                    }
                    Retail::build()->insert($res);
                }
            }
        }
        return true;
    }
}
