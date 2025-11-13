<?php

namespace app\common\validate;

use think\Validate;

/**
 * 配置-校验
 * User: Yacon
 * Date: 2022-07-20
 * Time: 13:25
 */
class Config extends Validate
{
    protected $rule = [
        'key' => 'require|checkKey',
        'content' => 'require',
        'value' => 'require',
        'data'=>'require',
    ];

    protected $field = [
        'content' => '说明',
        'value' => '内容',
        'key' => 'key',
        'data' => '数据',
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['data'],
        'edit' => ['value'],
        'csave'=> ['key','value','content'],
    ];
    protected function checkKey($value, $rule, $data){
        if(\app\api\model\Config::where('key', $value)->where('is_deleted',1)->where('site_id',$data['site_id'])->find()){
            return 'key已存在';
        }
        return true;
    }
}
