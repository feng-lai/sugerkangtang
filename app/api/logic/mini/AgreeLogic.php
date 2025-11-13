<?php

namespace app\api\logic\mini;

use app\api\model\Captcha;
use app\api\model\Interest;
use app\api\model\InterestBirthday;
use app\api\model\Level;
use app\api\model\Message;
use app\api\model\OrderSetting;
use app\api\model\Contestant;
use app\api\model\Agree;
use app\api\model\UserInterrest;
use app\api\model\UserToken;
use think\Exception;
use think\Db;

/**
 * 点赞-逻辑
 * User: Yacon
 * Date: 2022-07-21
 * Time: 14:31
 */
class AgreeLogic
{


    static public function miniAdd($request, $userInfo)
    {
        try {
            Contestant::build()->where('uuid', $request['contestant_uuid'])->findOrFail();
            if ($request['type'] == 1 && !Agree::build()->where(['contestant_uuid' => $request['contestant_uuid'], 'user_uuid' => $userInfo['uuid']])->count()) {
                $agree = Agree::build();
                $agree->uuid = uuid();
                $agree->contestant_uuid = $request['contestant_uuid'];
                $agree->matchs_step_uuid = $request['matchs_step_uuid'];
                $agree->user_uuid = $userInfo['uuid'];
                $agree->create_time = date("Y-m-d H:i:s", time());
                $agree->update_time = date("Y-m-d H:i:s", time());
                $agree->save();
                Contestant::build()->where('uuid', $request['contestant_uuid'])->setInc('agree');
            }
            if ($request['type'] == 2) {
                Agree::build()->where(['contestant_uuid' => $request['contestant_uuid'], 'user_uuid' => $userInfo['uuid']])->delete();
                Contestant::build()->where('uuid', $request['contestant_uuid'])->setDec('agree');
            }
            return true;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }


}
