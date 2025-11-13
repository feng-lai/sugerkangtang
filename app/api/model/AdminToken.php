<?php

namespace app\api\model;


/**
 * 管理员用户Token-模型
 * User: Yacon
 * Date: 2022-07-20
 * Time: 13:49
 */
class AdminToken extends BaseModel
{
    public static function build()
    {
        return new self();
    }


    public function vali($token)
    {
        $time = now_time(time());
        $where = "token='{$token}' and expiry_time>'{$time}'";
        $list = $this->alias('a')->join('admin b', 'a.admin_uuid=b.uuid')->where($where)->field('b.*')->find();
        if ($list) {
            $list['role_uuid'] = json_decode($list['role_uuid']);
            return $list;
        } else {
            return self::returnmsg(401);
        }
    }
    public function vali2($token)
    {
        $time = now_time(time());
        $where = "token='{$token}' and expiry_time>'{$time}'";
        $list = $this->alias('a')->join('admin b', 'a.admin_uuid=b.uuid')->where($where)->field('b.*')->find();
        if ($list) {
            return $list;
        } else {
            return '';
        }
    }
}
