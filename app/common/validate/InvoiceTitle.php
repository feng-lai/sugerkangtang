<?php

namespace app\common\validate;

use think\Validate;

/**
 * 发票抬头-校验
 */
class InvoiceTitle extends Validate
{
    protected $rule = [
        'type' => 'require|checkType',
        'title' => 'require|checkTitle',
        'number' => 'require',
        'bank' => 'require',
        'bank_number' => 'require',
        'address' => 'require',
        'phone' => 'require',
    ];

    protected $field = [
        'type' => '类型',
        'title' => '发票抬头',
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['type', 'title'],
        'edit' => [],
    ];

    protected function checkTitle($value, $rule, $data){
        $where = [
            'title' => $value,
            'is_deleted'=>1,
            'type'=>$data['type'],
        ];
        if(isset($data['site_id'])){
            $where['site_id'] = $data['site_id'];
        }
        if(isset($data['uuid']) && $data['uuid']){
            $where['uuid'] = ['<>', $data['uuid']];
        }
        if(\app\api\model\InvoiceTitle::where($where)->count() > 0){
            return '发票抬头已存在';
        }
        return true;
    }

    protected function checkType($value, $rule, $data){
        if($value == 2){
            if(!isset($data['number']) || empty($data['number'])){
                return '税号不能为空';
            }
        }
        return true;
    }
}
