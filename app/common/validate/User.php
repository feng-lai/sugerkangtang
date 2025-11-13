<?php

namespace app\common\validate;

use think\Validate;

/**
 * 用户列表-校验
 * User: Yacon
 * Date: 2022-07-20
 * Time: 19:38
 */
class User extends Validate
{
    protected $rule = [
        'gender' => 'number',
        'disabled' => 'require|in:1,2|checkDisabled',
        'disabled_note' => 'require',
        'type'=>'require|number|in:1,2',
        'code'=>'require|number',
        'phone'=>'require|number|checkPhone',
    ];

    protected $field = [
        'nickname' => '昵称',
        'phone' => '手机号',
        'avatar' => '头像',
        'gender' => '性别',
        'birthday' => '生日',
        'disabled' => '状态',
        'disabled_note' => '备注',
        'type'=>'类型',
        'code'=>'验证码'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => [],
        'edit' => [],
        'setDisabled' => ['disabled'],
        'userPhone'=>['phone','code'],
    ];
    protected function checkDisabled($value, $rule, $data){
        if($value == 2){
            if(!$data['disabled_note']){
                return '备注不能为空';
            }
            if(!$data['type']){
                return '类型不能为空';
            }
        }
        return true;
    }

    protected function checkPhone($value, $rule, $data){
        $where = ['site_id'=>1,'phone'=>$value,'is_deleted'=>1];
        if(isset($data['uuid']) && $data['uuid']){
            $where['uuid'] = ['<>',$data['uuid']];
        }
        if(\app\api\model\User::where($where)->count()){
            return ['msg'=>'手机号已存在'];
        }
        return true;
    }
}
