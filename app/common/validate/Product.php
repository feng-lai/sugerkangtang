<?php

namespace app\common\validate;

use think\Validate;

/**
 * 商品-校验
 */
class Product extends Validate
{
    protected $rule = [
        'name' => 'require|checkName',
        'code' => 'require|checkCode',
        'main_img' => 'require',
        'img'=>'require',
        'video'=>'require',
        'category_uuid' => 'require',
        'selling_point' => 'require',
        'after_sale_day' => 'require',
        'is_after_sale' => 'require|in:1,2',
        'qty' => 'require|integer',
        'price' => 'require',
        'original_price' => 'require',
        'is_original_price' => 'require|in:1,2',
        'desc'=>'require',
        'vis'=>'require',
        'parameter'=>'require|checkParameter',
        'attribute'=>'require|checkAttribute',
        'recommend'=>'require|in:1,2',
        'effect'=>'require',
        'suitable_for'=>'require',
        'product_type'=>'require',
    ];

    protected $field = [
        'name' => '名称',
        'img' => '图片',
        'code' => '编码',
        'main_img'=>'主图',
        'video'=>'视频',
        'category_uuid' => '分类',
        'selling_point' => '卖点',
        'after_sale_day' => '确认售后天数内能售后',
        'is_after_sale' => '是否允许售后',
        'qty' => '库存',
        'price' => '售价',
        'original_price' => '原价',
        'is_original_price' => '是否显示原价',
        'desc'=>'详情',
        'vis'=>'状态',
        'parameter'=>'参数',
        'attribute'=>'规格',
        'recommend'=>'是否推荐',
        'effect'=>'功效',
        'suitable_for'=>'使用人群',
        'product_type'=>'产品类型',
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['name','img','code','main_img','category_uuid','is_after_sale','qty','price','is_original_price','desc','parameter','attribute','effect','suitable_for','product_type'],
        'setVis'=>['vis'],
        'edit' => [],
        'setCategory'=>['category_uuid'],
        'setRecommend'=>['recommend'],
    ];

    protected function checkParameter($value, $rule, $data)
    {
        if(!is_array($value)){
            return false;
        }
        if(!isset($value[0]['parameter_uuid']) || !isset($value[0]['value'])){
            return false;
        }
        return true;
    }

    protected function checkAttribute($value, $rule, $data)
    {
        if(!is_array($value)){
            return false;
        }
        return true;
    }
    protected function checkName($value, $rule, $data){
        $where = [
            'name'=>$data['name'],
            'is_deleted'=>1
        ];
        if(isset($data['uuid'])){
            $where['uuid'] = ['<>', $data['uuid']];
        }
        if(\app\api\model\Product::build()->where($where)->count()){
            return '名称已存在';
        }
        return true;
    }

    protected function checkCode($value, $rule, $data){
        $where = [
            'code'=>$data['code'],
            'is_deleted'=>1
        ];
        if(isset($data['uuid'])){
            $where['uuid'] = ['<>', $data['uuid']];
        }
        if(\app\api\model\Product::build()->where($where)->count()){
            return '编码已存在';
        }
        return true;
    }
}
