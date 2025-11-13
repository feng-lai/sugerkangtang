<?php

namespace app\common\validate;

use think\Validate;

/**
 * 规格库-校验
 */
class Attribute extends Validate
{
    protected $rule = [
        'name' => 'require|checkName',
        'status' => 'require|in:1,2',
        'value' => 'require',
    ];

    protected $field = [
        'name' => '名称',
        'status' => '状态',
        'value'=>'规格值'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['name', 'status', 'value'],
        'setStatus'=>['status'],
        'edit' => [],
    ];

    protected function checkName($value, $rule, $data){
        $where = [
            'name'=>$data['name'],
            'is_deleted'=>1
        ];
        if(isset($data['uuid'])){
            $where['uuid'] = ['<>', $data['uuid']];
        }
        if(\app\api\model\Attribute::build()->where($where)->count()){
            return '名称已存在';
        }
        return true;
    }
}
