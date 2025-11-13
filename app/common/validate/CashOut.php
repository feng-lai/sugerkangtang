<?php

namespace app\common\validate;

use think\Validate;

/**
 * 提现-校验
 * User:
 * Date: 2022-07-20
 * Time: 13:25
 */
class CashOut extends Validate
{
    protected $rule = [
        'price' => 'require',
        'bank_card_uuid' => 'require',
        'note'=>'require',
        'status'=>'require|in:2,3|checkStatus',
        'reason'=>'require',
        'img'=>'require',
    ];

    protected $field = [
        'price' => '提现金额',
        'bank_card_uuid' => '银行卡uuid',
        'note'=>'备注',
        'status'=>'审核状态',
        'reason'=>'拒绝原因',
        'img'=>'交易凭证'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['price', 'bank_card_uuid'],
        'update' => ['status'],
        'setNote' => ['note'],
        'setStatus' => ['status'],
    ];

    protected function checkStatus($value, $rule, $data){
        if(\app\api\model\CashOut::build()->where('cash_out_id',$data['cash_out_id'])->value('status') != 1){
            return '非待审核状态';
        }
        if($value == 2){
            if(!isset($data['img']) || !$data['img']){
                return '请上传交易凭证';
            }
        }
        if($value == 3){
            if(!isset($data['reason']) || !$data['reason']){
                return '请填写拒绝原因';
            }
        }        return true;
    }
}
