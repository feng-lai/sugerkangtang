<?php

namespace app\api\model;
use think\Db;

/**
 * 商品-模型
 * User:
 * Date:
 * Time:
 */
class Product extends BaseModel
{
    public static function build()
    {
        return new self();
    }
    public function getImgAttr($value)
    {
        return json_decode($value);
    }

    public function setImgAttr($value)
    {
        return json_encode($value);
    }

    public function getEffectAttr($value)
    {
        return json_decode($value);
    }

    public function setEffectAttr($value)
    {
        return json_encode($value);
    }

    public function getSuitableForAttr($value)
    {
        return json_decode($value);
    }

    public function setSuitableForAttr($value)
    {
        return json_encode($value);
    }




    public function saveParameter($data){
        //先删除
        ProductParameter::build()->where(['site_id'=>$data['site_id'],'product_uuid'=>$data['uuid']])->delete();
        $res = $data['parameter'];

        foreach ($res as $k => $v){
            $res[$k]['uuid'] = uuid();
            $res[$k]['site_id'] = $data['site_id'];
            $res[$k]['product_uuid'] = $data['uuid'];
            $res[$k]['create_time'] = now_time(time());
            $res[$k]['update_time'] = now_time(time());

        }
        ProductParameter::build()->insertAll($res);
    }

    public function saveAttribute($data){
        //先删除
        ProductAttribute::build()->where(['site_id'=>$data['site_id'],'product_uuid'=>$data['uuid']])->update(['is_deleted'=>2]);
        $res = $data['attribute'];
        $qty = 0;
        foreach ($res as $k => $v){
            $res[$k]['uuid'] = uuid();
            $res[$k]['site_id'] = $data['site_id'];
            $res[$k]['product_uuid'] = $data['uuid'];
            //$res[$k]['code'] = Db::raw("'".$v['code']."'");
            $res[$k]['create_time'] = now_time(time());
            $res[$k]['update_time'] = now_time(time());
            $qty += $v['qty'];
        }
        ProductAttribute::build()->insertAll($res);
        return $qty;
    }
}
