<?php

namespace app\common\validate;

use think\Validate;

/**
 * 分销员-校验
 */
class Partner extends Validate
{
    protected $rule = [
        'name' => 'require|checkName',
        'user_uuid' => 'require',
        'admin_uuid' => 'require',
        'puuid' => 'require',
        'ppuuid' => 'require',
        'level' => 'require|integer',
        'type' => 'require|in:1,2',
        'phone'=>'require|checkPhone',
        'address' => 'require',
        'address_detail' => 'require',
        'contact_name' => 'require',
        'wallet'=>'require',
        'bank_name' => 'require',
        'bank_number' => 'require',
        'note' => 'require',
        'certificate' => 'require',
        'business_license' => 'require',
        'protocol'=>'require',
        'cash_out_perstent' => 'require',
        'cash_out_low' => 'require',
        'status' => 'require|in:1,2',
        'review_status' => 'require|in:2,3|checkReviewStatus',
    ];

    protected $field = [
        'name' => '高级合伙人名称',
        'user_uuid' => '用户uuid',
        'admin_uuid' => '管理员uuid',
        'puuid' => '上级uuid',
        'ppuuid' => '一级uuid',
        'level' => '层级',
        'type' => '类型',
        'phone'=>'手机号',
        'address' => '地址',
        'address_detail' => '详细地址',
        'contact_name' => '联系人姓名',
        'wallet'=>'余额',
        'bank_name' => '银行卡名称',
        'bank_number' => '银行卡号',
        'note' => '备注',
        'certificate' => '证书图片',
        'business_license' => '营业执照',
        'protocol'=>'合作协议',
        'cash_out_perstent' => '体现手续比例',
        'cash_out_low' => '最低提现金额',
        'status'=>'状态',
        'review_status'=>'审核状态'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['name', 'contact_name', 'phone', 'address', 'address_detail'],
        'setStatus' => ['status'],
        'setType' => ['type'],
        'setNote' => ['note'],
        'setReviewStatus' => ['review_status'],
        'miniSave'=>['name', 'contact_name', 'phone', 'address', 'address_detail','bank_name','bank_number','business_license','protocol']
    ];

    protected function checkReviewStatus($value, $rule, $data)
    {
        if($value == 3){
            if(!$data['note']){
                return '请填写原因';
            }
        }
        if($value == 2){
            if(!$data['certificate']){
                return '请上传证书图片';
            }
        }
        return true;
    }

    protected function checkPhone($value, $rule, $data){
        if(!\app\api\model\User::where('phone',$value)->where('is_deleted',1)->find() && !isset($data['sence'])){
            return '当前手机号的注册用户不存在';
        }
        $where['is_deleted'] = 1;
        $where['phone'] = $value;
        if(isset($data['uuid']) && $data['uuid']){
            $where['uuid'] = ['<>', $data['uuid']];
        }
        if(\app\api\model\Partner::build()->where($where)->count()){
            return '手机号已存在';
        }
        return true;
    }

    protected function checkName($value, $rule, $data){
        $where['is_deleted'] = 1;
        $where['name'] = $value;
        if(isset($data['uuid']) && $data['uuid']){
            $where['uuid'] = ['<>', $data['uuid']];
        }
        if(\app\api\model\Partner::build()->where($where)->count()){
            return '名称已存在';
        }
        return true;
    }

}
