<?php

/**
 * Author Yacon
 * Date 2022/02/15 16:45
 */

namespace app\api\logic\common;

use app\api\model\Config;
use app\api\model\Partner;
use app\api\model\PartnerReview;
use think\Db;

class UserToPartnerLogic
{

    public static function sync()
    {
        $BeSeniorPartner = Config::build()->where('key','BeSeniorPartner')->value('value');
        Partner::build()
            ->alias('p')
            ->join('partner_review pr', 'pr.user_uuid = p.user_uuid and pr.is_deleted = 1','left')
            ->field([
                'p.uuid',
                'p.name',
                'p.user_uuid',
                'p.site_id'
            ])
            ->where('pr.uuid',null)
            ->where('p.is_deleted',1)
            ->where('p.type',1)
            ->where(function($query) use ($BeSeniorPartner) {
                $query->whereExp('', '(SELECT COUNT(1) as c FROM user WHERE user.invite_partner_uuid = p.user_uuid) >= ' . $BeSeniorPartner);
            })
            ->select()->each(function($item) {
                PartnerReview::build()->save([
                    'uuid' =>uuid(),
                    'user_uuid'=>$item->user_uuid,
                    'review_status'=>1,
                    'site_id'=>$item->site_id,
                    'create_time'=>now_time(time()),
                    'update_time'=>now_time(time()),
                ]);
            });
    }
}
