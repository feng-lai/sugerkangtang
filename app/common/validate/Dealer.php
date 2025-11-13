<?php

namespace app\common\validate;

use think\Validate;

/**
 * 特邀经销商-校验
 */
class Dealer extends Validate
{
    protected $rule = [
        'name' => 'require|checkName',
        'phone' => 'require',
        'address' => 'require',
        'address_detail' => 'require',
        'bank' => 'require',
        'bank_number' => 'require',
        'note'=>'require',
        'admin_uuid'=>'require',
        'contact_name'=>'require',
        'uname'=>'require|checkUname',
        'status'=>'require|in:1,2',
        'producer_uuid'=>'require|checkProducerUuid',
    ];

    protected $field = [
        'name' => '名称',
        'phone' => '联系电话',
        'address' => '省市区',
        'address_detail' => '详细地址',
        'bank' => '收款银行',
        'bank_number' => '银行卡号',
        'note'=>'备注',
        'contact_name'=>'联系人姓名',
        'uname'=>'登录账号',
        'status'=>'状态',
        'producer_uuid'=>'出品方uuid'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['name', 'phone', 'address', 'address_detail','uname','contact_name'],
        'edit' => ['name', 'phone', 'address', 'address_detail', 'contact_name'],
        'setStatus' => ['status'],
    ];


    protected function checkName($value, $rule, $data){
        $where = [
            'is_deleted'=>1,
            'site_id'=>$data['site_id'],
            'name'=>$value,
        ];
        if(isset($data['uuid']) && $data['uuid']){
            $where['uuid'] = ['<>',$data['uuid']];
        }
        if(\app\api\model\Dealer::where($where)->find()){
            return '名称已存在';
        }
        return true;
    }

    protected function checkUname($value, $rule, $data){
        $where = [
            'is_deleted'=>1,
            'uname'=>$value,
        ];
        if(isset($data['uuid']) && $data['uuid']){
            $admin_uuid = \app\api\model\Dealer::build()->where('uuid',$data['uuid'])->value('admin_uuid');
            $where['uuid'] = ['<>',$admin_uuid];
        }

        if(\app\api\model\Admin::build()->where($where)->find()){
            return '管理员账号已存在';
        }
        return true;
    }
    protected function checkProducerUuid($value, $rule, $data){
        if(!\app\api\model\Producer::build()->where('uuid',$value)->where('is_deleted',1)->count()){
            return ['msg'=>'出品方uuid不存在，请检查参数是否正确'];
        }
        return true;
    }
}
