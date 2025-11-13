<?php

/**
 * Created by PhpStorm.
 * User: Json
 */

namespace app\common\validate;

use app\api\model\Captcha;
use think\Validate;

class SendSmsCode extends Validate
{
    protected $regex = [
        'mobile' => '1[23456789]\d{9}',
    ];

    protected $rule = [
        'mobile' => 'require|max:11|regex:mobile',
        'type' => 'require|in:1',
        'code' => 'require|number|max:6',
    ];

    protected $field = [
        'mobile' => '手机号',
        'type' => '业务类型',
        'code' => '验证码',
    ];

    protected $message = [];

    protected $scene = [
        'save' => ['mobile', 'type'],
        'save_one' => ['mobile', 'code'],
        'captcha' => ['mobile'],
        'app_sms_login' => ['mobile', 'code'],
        'account_delete' => ['code'],
    ];

    /**
     * @author: Json - Cove-Rudy
     * @time: 2018年5月7
     * description:修改规则
     * @param string $key rule的key
     * @param string $value 修改的内容
     * @param bool $append 是否追加,true为追加,false为重置
     * @return $this
     */
    public function changeRule($key, $value, $append = false)
    {
        if (isset($this->rule[$key]) && is_string($value)) {
            $append ? $this->rule[$key] = $this->rule[$key] . $value : $this->rule[$key] = $value;
        }
        return $this;
    }
}
