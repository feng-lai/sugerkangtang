<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use app\api\model\Attribute;
use think\Exception;

/**
 * 商品规格-控制器
 */
class ProductAttribute extends Api
{
    public $restMethodList = 'get';

    public function index()
    {
        $request = $this->selectParam([
            'site_id',
            'product_uuid'
        ]);
        if(!$request['product_uuid']){
            $this->returnmsg(400, [], [], '', '', 'product_uuid不能为空');
        }
        $where = [
            'is_deleted'=>1,
            'product_uuid'=>$request['product_uuid']
        ];
        $request['site_id']?$where['site_id']=$request['site_id']:'';
        $result = \app\api\model\ProductAttribute::build()->field('uuid,img,attribute_uuid,attribute_value,qty,price')->where($where)->select()->each(function($item){
            $item->attribute_name = Attribute::build()->where('uuid',$item['attribute_uuid'])->value('name');
            unset($item->attribute_uuid);
        });
        $this->render(200, ['result' => $result]);
    }

    public function read($id)
    {
        $result = \app\api\model\Banner::build()->where(['uuid'=>$id])->find();
        $this->render(200, ['result' => $result]);
    }
}
