<?php

namespace app\common\validate;

use think\Validate;

/**
 * 字典类型-校验
 */
class DictionaryType extends Validate
{
    protected $rule = [
        'name' => 'require',
        'type' => 'require|checkType',
        'status' => 'require|in:1,2',
        'note' => 'require',
    ];

    protected $field = [
        'name' => '名称',
        'type' => '类型',
        'status'=>'状态',
        'note'=>'备注'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['name','type','status'],
        'setStatus' => ['status'],
    ];
    protected function checkType($value, $rule, $data){
        $where = [
            'is_deleted'=>1,
            'type'=>$data['type'],
        ];
        if(isset($data['site_id'])){
            $where['site_id'] = $data['site_id'];
        }
        if(isset($data['uuid']) && $data['uuid']){
            $where['uuid'] = ['<>', $data['uuid']];
        }
        if(\app\api\model\DictionaryType::where($where)->count() > 0){
            return '类型已存在';
        }
        return true;
    }

}
