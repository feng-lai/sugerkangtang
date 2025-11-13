<?php

namespace app\common\validate;

use think\Validate;

/**
 * 原因-校验
 */
class Reason extends Validate
{
    protected $rule = [
        'content' => 'require|checkContent',
        'status'=>'require|between:1,2',
        'type'=>'require|between:1,2',
    ];

    protected $field = [
        'content' => '原因',
        'status'=>'状态',
        'type'=>'上移/下移'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['content', 'status','type'],
        'edit' => ['content', 'status'],
        'setOrderNumber'=>['type']
    ];

    protected function checkContent($value, $rule, $data){
        $where['is_deleted'] = 1;
        $where['content'] = $value;

        if(isset($data['uuid']) && $data['uuid']){
            $where['uuid'] = ['<>',$data['uuid']];
        }else{
            $where['site_id'] = $data['site_id'];
            $where['type'] = $data['type'];
        }
        if(\app\api\model\Reason::where($where)->find()){
            return '原因已存在';
        }
        return true;
    }
}
