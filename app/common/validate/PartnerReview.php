<?php

namespace app\common\validate;

use think\Validate;

/**
 * 合伙人审核-校验
 */
class PartnerReview extends Validate
{
    protected $rule = [
        'note' => 'require',
        'review_status' => 'require|in:2,3|checkReviewStatus',
    ];

    protected $field = [
        'note' => '备注',
        'review_status'=>'审核状态'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['review_status'],
        'setNote' => ['note'],
    ];

    protected function checkReviewStatus($value, $rule, $data)
    {
        if($value == 3){
            if(!$data['note']){
                return '请填写原因';
            }
        }
        return true;
    }

}
