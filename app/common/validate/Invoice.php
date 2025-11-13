<?php

namespace app\common\validate;

use think\Validate;

/**
 * 发票-校验
 */
class Invoice extends Validate
{
    protected $rule = [
        'type' => 'require|checkType',
        'title' => 'require',
        'order_id' => 'require',
        'file' => 'require',
        'note' => 'require',
        'status' => 'require|in:2,3|checkStatus',
    ];

    protected $field = [
        'type' => '类型',
        'title' => '发票抬头',
        'order_id'=>'订单号',
        'file'=>'发票附件',
        'note'=>'备注',
        'status'=>'状态'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['type', 'title','order_id'],
        'edit' => ['type', 'title'],
        'file_note' => ['status'],
    ];
    protected function checkType($value, $rule, $data){
        if($value == 2){
            if(!isset($data['number']) || empty($data['number'])){
                return '税号不能为空';
            }
        }
        return true;
    }

    protected function checkStatus($value, $rule, $data){
        if(\app\api\model\Invoice::build()->where(['uuid' => $data['uuid']])->value('status') != 1){
            return '非待审核状态';
        }
        if($value == 2){
            if(!isset($data['file']) || empty($data['file'])){
                return '请上传附件';
            }
        }
        return true;
    }

}
