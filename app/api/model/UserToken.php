<?php

namespace app\api\model;

use think\Exception;

/**
 * 用户Token-模型
 * User: Yacon
 * Date: 2022-07-21
 * Time: 08:58
 */
class UserToken extends BaseModel
{
    public static function build()
    {
        return new self();
    }


    public function vali($token)
    {
        $time = now_time(time());
        $where = "token='{$token}' and expiry_time>'{$time}'";
        $list = $this->alias('a')->join('user b', 'a.user_uuid=b.uuid')->where($where)->field('b.*')->find();
        if ($list) {
            if ($list['disabled'] == 2) {
                if( !$list['disabled_end_time'] || strtotime($list['disabled_end_time']) > time()){
                    return self::returnmsg(402, [], [], "DISABLED", "",'被封禁');
                }
            }
            return $list;
        } else {
            return self::returnmsg(401);
        }
    }

    public function vali2($token)
    {
        $time = now_time(time());
        $where = "token='{$token}' and expiry_time>'{$time}'";
        $list = $this->alias('a')->join('user b', 'a.user_uuid=b.uuid')->where($where)->field('b.*')->find();
        if ($list) {
            return $list;
        } else {
            return '';
        }
    }
}
