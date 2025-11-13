<?php

namespace app\common\validate;

use think\Validate;

/**
 * 商品参数-校验
 */
class Parameter extends Validate
{
    protected $rule = [
        'category_uuid' => 'require',
        'type' => 'require|in:1,2,3,4|checkType',
        'status' => 'require|in:1,2',
        'name' => 'require|checkName',
    ];

    protected $field = [
        'category_uuid' => '分类',
        'status' => '状态',
        'name' => '名称',
        'type'=>'类型'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['name', 'status','type'],
        'vis'=>['vis'],
        'edit' => [],
        'setStatus' => ['status'],
    ];

    protected function checkType($value, $rule, $data)
    {
        if($value == 1) {
            if(!$data['category_uuid']) {
                return '分类不能为空';
            }
        }
        return true;
    }

    protected function checkName($value, $rule, $data){
        $where = [
            'name'=>$data['name'],
            'is_deleted'=>1,
            'type'=>$data['type']
        ];
        if(isset($data['uuid'])){
            $where['uuid'] = ['<>', $data['uuid']];
        }else{
            $where['site_id'] = $data['site_id'];
        }
        if(\app\api\model\Parameter::build()->where($where)->count()){
            return '名称已存在';
        }
        return true;
    }
}
