<?php

namespace app\api\logic\mini;

use app\api\model\Category;
use app\api\model\Product;
use app\api\model\Collect;
use app\api\model\ProductAttribute;
use app\api\model\ProductParameter;
use think\Exception;
use think\Db;

/**
 * 商品-逻辑
 */
class ProductLogic
{
    static public function List($request)
    {
        try {
            $where = ['is_deleted' => 1, 'vis' => 1];
            if ($request['category_uuid']) {
                $where['category_uuid'] = $request['category_uuid'];
            }
            if ($request['site_id']) {
                $where['site_id'] = $request['site_id'];
            }
            if ($request['product_type']) {
                $where['product_type'] = ['in', explode(',', $request['product_type'])];
            }
            if ($request['name']) {
                $where['name'] = ['like', '%' . $request['name'] . '%'];
            }
            $order = 'create_time desc';
            if($request['recommend']){
                $where['recommend'] = $request['recommend'];
                if($request['recommend'] == 1){
                    $order = 'order_number desc';
                }
            }
            if ($request['price_order']) {
                $order = 'price ' . $request['price_order'];
            }
            if ($request['sale_order']) {
                $order = 'sale ' . $request['sale_order'];
            }

            $result = Product::build()
                ->field('
                    uuid,
                    name,
                    recommend,
                    main_img,
                    price,
                    original_price,
                    is_original_price,
                    selling_point,
                    order_number,
                    (select sum(sale) from product_attribute where product_uuid = product.uuid) as sale
                ')
                ->where($where)
                ->order($order);
            if ($request['effect']) {
                $result = $result->where(function ($q) use ($request, $where) {
                    foreach (explode(',', $request['effect']) as $value) {
                        $q->whereOrRaw("JSON_CONTAINS(effect, '\"$value\"')");
                    }
                });
            }
            if ($request['suitable_for']) {
                $result = $result->where(function ($q) use ($request, $where) {
                    foreach (explode(',', $request['suitable_for']) as $value) {
                        $q->whereOrRaw("JSON_CONTAINS(suitable_for, '\"$value\"')");
                    }
                });
            }
            $result = $result->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
            return $result;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Detail($uuid, $userInfo)
    {
        try {
            $data = Product::build()->field('
            uuid,
            name,
            main_img,
            img,
            video,
            category_uuid,
            selling_point,
            qty,
            price,
            original_price,
            is_original_price,
            is_after_sale,
            after_sale_day,
            desc,
            vis,
            create_time
            ')->where('uuid', $uuid)->where('is_deleted', 1)->findOrFail();
            $data->category_name = Category::build()->where('uuid', $data->category_uuid)->value('name');
            //是否收藏
            $data->isCollect = 0;
            if ($userInfo) {
                $data->isCollect = Collect::build()->where('user_uuid', $userInfo['uuid'])->where('product_uuid', $uuid)->where('is_deleted', 1)->count();
            }
            $data->attribute = ProductAttribute::build()
                ->field('a.name,pa.attribute_uuid')
                ->alias('pa')
                ->join('attribute a', 'a.uuid = pa.attribute_uuid','left')
                ->where('pa.product_uuid', $uuid)
                ->where('pa.is_deleted', 1)
                ->group('pa.attribute_uuid')
                ->select()->each(function ($item) use ($uuid) {
                    $item->child = ProductAttribute::build()
                        ->field('pa.uuid,pa.attribute_value,pa.img,pa.price,pa.qty')
                        ->alias('pa')
                        ->join('attribute a', 'a.uuid = pa.attribute_uuid','left')
                        ->where('pa.product_uuid', $uuid)
                        ->where('pa.attribute_uuid', $item->attribute_uuid)
                        ->where('pa.is_deleted', 1)
                        ->select();
                    unset($item->attribute_uuid);
                });


            $data->parmeter = ProductParameter::build()
                ->field('p.uuid,pa.name as parameter_name,p.value')
                ->alias('p')
                ->join('parameter pa', 'pa.uuid = p.parameter_uuid')
                ->where('p.product_uuid', $uuid)
                ->where('p.is_deleted', 1)
                ->select();


            return $data;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
