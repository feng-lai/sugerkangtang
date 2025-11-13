<?php

namespace app\common\validate;

use think\Validate;

/**
 * 专家资料-校验
 */
class Expert extends Validate
{
    protected $rule = [
        'type' => 'require|in:1,2',
        'title' => 'require|checkTitle',
        'file' => 'require',
        'status' => 'require|in:1,2',
        'desc'=>'require',
    ];

    protected $field = [
        'type' => '类型',
        'title' => '标题',
        'file' => '图片/视频',
        'status'=>'状态',
        'desc'=>'介绍'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['type','title','file','desc'],
        'setStatus' => ['status'],
    ];

    protected function checkTitle($value, $rule, $data){
        $where = [
            'title'=>$data['title'],
            'is_deleted'=>1
        ];
        if(isset($data['uuid'])){
            $where['uuid'] = ['<>', $data['uuid']];
        }
        if(\app\api\model\Expert::build()->where($where)->count()){
            return '标题已存在';
        }
        return true;
    }


}
