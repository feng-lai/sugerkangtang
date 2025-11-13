<?php

namespace app\common\validate;

use app\api\model\ProductAttribute;
use think\Validate;

/**
 * 订单-校验
 * User:
 * Date: 2022-07-20
 * Time: 13:25
 */
class Order extends Validate
{
    protected $rule = [
        'address_uuid' => 'require',
        'product' => 'require|checkProduct',
        'medical_report_uuid' => 'require',
        'note' => 'require',
        'type' => 'require',
        'reason' => 'require',
    ];

    protected $field = [
        'address_uuid' => '地址',
        'product' => '商品信息',
        'medical_report_uuid' => '体检报告',
        'note' => '备注',
        'type' => '类型',
        'reason'=>'原因'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['address_uuid', 'product', 'type'],
        'edit' => [],
        'cancel' => ['reason'],
        'setNote' => ['note'],
        'setMedicalReport'=>['medical_report_uuid'],
    ];

    protected function checkProduct($value, $rule, $data)
    {
        if (!is_array($value)) {
            return false;
        }
        if (!isset($value[0]['product_attribute_uuid']) || !isset($value[0]['product_attribute_uuid'])) {
            return false;
        }
        if (!isset($value[0]['qty']) || !isset($value[0]['qty'])) {
            return false;
        }
        return true;
    }
}
