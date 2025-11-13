<?php

namespace app\common\validate;

use think\Validate;

/**
 * 分销订单-校验
 */
class CommissionOrder extends Validate
{
    protected $rule = [
        'parameter' => 'require|checkParameter',
        'status' => 'require|in:2,3',
    ];

    protected $field = [
        'parameter' => '结算数据',
        'status' => '结算状态',
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['status','parameter'],
        'edit' => [],
    ];

    protected function checkParameter($value, $rule, $data){
        if(!is_array($value)){
            return 'parameter参数有误';
        }
        return true;
    }
}
