<?php

/**
 * Created by GYMOO Template.
 * User: Jason Lau
 * FileName: Coupon.php
 * Email: jason@gymoo.cn
 * Date: 2019-04-17
 * Time: 16:20
 */

namespace app\api\model;

use app\api\controller\Send;
use app\common\tools\RedisUtil;
use think\Exception;
use think\Model;

class Captcha extends Model
{
    use Send;
    protected $resultSetType = 'collection';
    /**
     * 表名,
     */
    protected $table = 'captcha';

    public static function build()
    {
        return new self();
    }

    /**
     * 验证码校验
     * @param $params
     */
    public function captchaCheck($params)
    {
        //if ($params['code'] == '1234') return true;
        try {
            $where = "code = '{$params['code']}' and user_mobile = '{$params['mobile']}'";
            $res = $this->where($where)->findOrFail();

            $code_time = strtotime($res['create_time']) + 300;
            if ($code_time < time()) {
                Captcha::build()->where('user_mobile', $params['mobile'])->delete();
                $this->returnmsg(403, [], [], 'Forbidden', "", '验证码超过有效期');
            } else {
                //销毁验证码
                Captcha::build()->where('user_mobile', $params['mobile'])->delete();
                return true;
            }
        } catch (Exception $e) {
            $this->returnmsg(403, [], [], 'Forbidden', $e->getMessage(), '验证码不符');
        }
    }
}
