<?php

namespace app\api\logic\mini;

use app\api\model\Cart;
use app\api\model\ProductAttribute;
use think\Exception;
use think\Db;

/**
 * 购物车-逻辑
 */
class CartLogic
{
    static public function Add($request, $userInfo)
    {
        try {
            $attr = ProductAttribute::build()->where('is_deleted',1)->where('uuid',$request['product_attribute_uuid'])->findOrFail();
            if($attr->qty < $request['qty']){
                return ['msg'=>'库存不足'];
            }
            $data = Cart::where(['user_uuid'=>$userInfo['uuid'],'product_attribute_uuid'=>$request['product_attribute_uuid'],'is_deleted'=>1])->find();
            if(!$data){
                $request['uuid'] = uuid();
                $request['user_uuid'] = $userInfo['uuid'];
                $request['create_time'] = now_time(time());
                $request['update_time'] = now_time(time());
                Cart::build()->save($request);
                return $request['uuid'];
            }else{
                $res = ['qty'=>$data['qty'],'update_time'=>now_time(time())];
                $request['invite_uuid']?$res['invite_uuid'] = $request['invite_uuid']:'';
                $data->save($res);
                return $data['uuid'];
            }

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }



    static public function List($request, $userInfo)
    {
        try {
            $where = [
                'c.is_deleted' => 1,
                'c.site_id' => $request['site_id'],
                'c.user_uuid' => $userInfo['uuid'],
            ];
            return Cart::build()
                ->field('c.uuid,c.product_attribute_uuid,c.invite_uuid,c.qty,p.uuid as product_uuid,p.name,p.vis,p.main_img,pa.price,p.original_price,p.is_original_price,pa.qty as product_attribute_qty,a.name as attribute_name,pa.attribute_value')
                ->alias('c')
                ->join('product_attribute pa','c.product_attribute_uuid=pa.uuid','left')
                ->join('product p','p.uuid=pa.product_uuid','left')
                ->join('attribute a','a.uuid=pa.attribute_uuid','left')
                ->where($where)
                ->order('c.create_time desc')
                ->select();
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
    static public function setQty($request, $userInfo)
    {
        try {
            $data = Cart::build()->where('uuid', $request['uuid'])->where('user_uuid', $userInfo['uuid'])->where('is_deleted', 1)->findOrFail();
            $res['update_time'] = now_time(time());
            if($request['qty']){
                if(ProductAttribute::build()->where('is_deleted',1)->where('uuid',$data['product_attribute_uuid'])->value('qty') < $request['qty']){
                    return ['msg'=>'库存不足'];
                }
                $res['qty'] = $request['qty'];
            }
            if($request['product_attribute_uuid']){
                $res['product_attribute_uuid'] = $request['product_attribute_uuid'];
            }
            $data->save($res);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Delete($uuid, $userInfo)
    {
        try {
            $data = Cart::build()->whereIn('uuid', $uuid)->where('user_uuid', $userInfo['uuid'])->where('is_deleted', 1)->update(['is_deleted' => 2]);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

}
