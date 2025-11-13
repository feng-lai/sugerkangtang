<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\Product;
use app\api\model\ProductAttribute;
use think\Exception;
use think\Db;

/**
 *商品-规格逻辑
 */
class ProductAttributeLogic
{
    static public function cmsList($request, $userInfo)
    {
        $where = ['a.is_deleted' => 1];
        if ($request['keyword']) {
            $where['p.name|a.code'] = ['like', '%' . $request['keyword'] . '%'];
        }
        if ($request['category_uuid']) {
            $where['p.category_uuid'] = ['=', $request['category_uuid']];
        }
        if ($request['qty_min']) {
            $where['a.qty'] = ['between', [$request['qty_min'], $request['qty_max']]];
        }
        if ($request['site_id']) {
            $where['site_id'] = ['=', $request['site_id']];
        }
        if($request['is_qty_danger'] && $request['is_qty_danger'] == 1){
            $where['a.qty'] = ['<',Db::raw('qty_danger')];
            $where['a.qty_danger'] = ['<>',0];
        }
        $result = ProductAttribute::build()
            ->field('a.uuid,p.name,a.code,at.name as attribute_name,a.attribute_value,a.price,a.qty,a.qty_danger,a.sale,a.update_time')
            ->where($where)
            ->alias('a')
            ->join('product p', 'p.uuid = a.product_uuid', 'left')
            ->join('attribute at', 'at.uuid = a.attribute_uuid', 'left')
            ->order('a.create_time desc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
        AdminLog::build()->add($userInfo['uuid'], '商品管理-参数库', '参数库列表');
        return $result;
    }


    static public function cmsEdit($request, $userInfo)
    {
        try {
            Db::startTrans();
            $data = ProductAttribute::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
            $res = [];
            $res['qty'] = $request['qty']?$request['qty']:0;
            $res['qty_danger'] = $request['qty_danger']?$request['qty_danger']:0;
            $data->save($res);
            if($request['qty']){
                Product::build()
                    ->where('uuid', $data->product_uuid)
                    ->update([
                        'qty' => ProductAttribute::build()->where('product_uuid', $data->product_uuid)->sum('qty')
                    ]);
            }
            Db::commit();
            AdminLog::build()->add($userInfo['uuid'], '商品管理-库存管理', '修改库存/库存预警');
            return true;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }


}
