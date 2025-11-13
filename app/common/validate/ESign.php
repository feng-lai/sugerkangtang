<?php

namespace app\common\validate;

use think\Validate;

/**
 * e签宝-校验
 */
class ESign extends Validate
{
    protected $rule = [
        'bank_card_uuid' => 'require',
    ];

    protected $field = [
        'bank_card_uuid' => '银行卡信息',
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['bank_card_uuid'],
        'edit' => [],
    ];
}
