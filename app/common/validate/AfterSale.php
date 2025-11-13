<?php

namespace app\common\validate;

use app\api\model\ProductAttribute;
use think\Validate;

/**
 * 售后-校验
 */
class AfterSale extends Validate
{
    protected $rule = [
        'order_id' => 'require',
        'product'=>'require|checkProduct',
        'reason' => 'require',
        'status'=>'require|in:2,3|checkStatus',
        'refuse_reason'=>'require',
        'refund_price'=>'require',
        'opinion'=>'require',
        'note'=>'require',
    ];

    protected $field = [
        'order_id' => '订单id',
        'reason'=>'原因',
        'product'=>'商品',
        'status'=>'状态',
        'refuse_reason'=>'拒绝原因',
        'refund_price'=>'退款金额',
        'opinion'=>'审批意见',
        'note'=>'备注'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['order_id', 'reason', 'product'],
        'edit' => [],
        'setStatus' => ['status'],
        'setNote' => ['note'],
    ];

    protected function checkProduct($value, $rule, $data){
        if(!is_array($value)) {
            return 'product_attribute_uuid非法参数';
        }
        return true;
    }

    protected function checkStatus($value, $rule, $data)
    {
        if($value == 2){
            if(!$data['refund_price']){
                return '请填写退款金额';
            }
        }
        if($value == 3){
            if(!$data['refuse_reason']){
                return '请填写拒绝原因';
            }
        }
        return true;
    }
}
