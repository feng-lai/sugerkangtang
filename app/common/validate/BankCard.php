<?php

namespace app\common\validate;

use think\Validate;

/**
 * 银行卡管理-校验
 */
class BankCard extends Validate
{
    protected $rule = [
        'name' => 'require',
        'phone' => 'require',
        'number' => 'require|checkNumber',
        'card_name' => 'require',
    ];

    protected $field = [
        'name' => '姓名',
        'phone' => '电话',
        'number' => '卡号',
        'card_name' => '银行卡名称',
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['name', 'phone', 'number', 'card_name'],
        'edit' => [],
    ];
    protected function checkNumber($value, $rule, $data)
    {
        $where = [
            'is_deleted'=>1,
            'user_uuid'=>$data['user_uuid'],
            'number'=>$value
        ];
        if(isset($data['uuid']) && $data['uuid']){
            $where['uuid'] = ['<>',$data['uuid']];
        }
        if(\app\api\model\BankCard::build()->where($where)->find()){
            return '银行卡号已存在';
        }
        return true;
    }
}
