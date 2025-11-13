<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\Category;
use app\api\model\Product;
use app\api\model\ProductAttribute;
use app\api\model\ProductParameter;
use think\Exception;
use think\Db;

/**
 *商品逻辑
 */
class ProductLogic
{
    static public function cmsList($request, $userInfo)
    {
        $where = ['is_deleted' => 1];
        if ($request['keyword']) {
            $where['name|code'] = ['like', '%' . $request['keyword'] . '%'];
        }
        if ($request['status']) {
            if ($request['status'] == 3) {
                $where['qty'] = 0;
            } else {
                $where['vis'] = ['=', $request['status']];
            }

        }
        if ($request['site_id']) {
            $where['site_id'] = ['=', $request['site_id']];
        }
        if ($request['category_uuid']) {
            $where['category_uuid'] = ['=', $request['category_uuid']];
        }        $order = ['create_time' , 'desc'];
        if ($request['recommend']) {
            $where['recommend'] = ['=', $request['recommend']];
            if($request['recommend'] == 1){
                $order = ['order_number' , 'desc'];
            }
        }
        if ($request['start_time']) {
            $where['create_time'] = ['between', [$request['start_time'], $request['end_time']]];
        }
        if ($request['recommend_start_time']) {
            $where['recommend_time'] = ['between', [$request['recommend_start_time'], $request['recommend_end_time']]];
        }

        if ($request['price_min']) {
            $where['price'] = ['between', [$request['price_min'], $request['price_max']]];
        }

        $result = Product::where($where)
            ->order($order[0],$order[1])
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function ($item) {
                $item->status = $item->qty == 0 ? 3 : $item->vis;
                $item->category_name = Category::build()->where('uuid', $item->category_uuid)->value('name');
                $item->sale = ProductAttribute::build()->where('product_uuid', $item->uuid)->sum('sale');
            });
        AdminLog::build()->add($userInfo['uuid'], '商品管理-商品库', '商品库列表');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = Product::build()->where('uuid', $id)->where('is_deleted', 1)->findOrFail();
        $data->attribute = ProductAttribute::build()->where(['product_uuid' => $data->uuid, 'is_deleted' => 1])->select();
        $data->parameter = ProductParameter::build()->where(['product_uuid' => $data->uuid, 'is_deleted' => 1])->select();
        AdminLog::build()->add($userInfo['uuid'], '商品管理-商品库', '商品库详情');
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            Db::startTrans();
            $request['create_time'] = now_time(time());
            $request['update_time'] = now_time(time());
            $request['uuid'] = uuid();

            Product::build()->saveParameter($request);
            $qty = Product::build()->saveAttribute($request);

            unset($request['parameter']);
            unset($request['attribute']);
            $request['qty'] = $qty;
            Product::build()->save($request);
            Db::commit();
            AdminLog::build()->add($userInfo['uuid'], '商品管理-商品库', '新增商品-' . $request['name']);
            return $request['uuid'];
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo)
    {
        try {
            Db::startTrans();
            $data = Product::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();

            $request['site_id'] = $data->site_id;
            Product::build()->saveParameter($request);
            Product::build()->saveAttribute($request);

            unset($request['parameter']);
            unset($request['attribute']);
            $request['code'] = Db::raw("'".$request['code']."'");
            $data->save($request);
            Db::commit();
            AdminLog::build()->add($userInfo['uuid'], '商品管理-商品库', '编辑商品-' . $request['name']);
            return true;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id, $userInfo)
    {
        try {
            Product::build()->whereIn('uuid', $id)->where('is_deleted', 1)->update(['is_deleted' => 2]);
            AdminLog::build()->add($userInfo['uuid'], '商品管理-商品库', '删除商品');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static function setVis($request, $userInfo, $uuid)
    {
        try {
            Product::build()->whereIn('uuid', $uuid)->where('is_deleted', 1)->update($request);
            AdminLog::build()->add($userInfo['uuid'], '商品管理-商品库', '设置状态');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static function setRecommend($request, $userInfo, $uuid)
    {
        try {
            if ($request['recommend'] == 1) {
                $request['recommend_time'] = now_time(time());
            }
            $order_number = Product::build()->where('recommend', 1)->where('is_deleted',1)->order('recommend desc')->value('order_number');
            $request['order_number'] = $order_number?$order_number+1:1;
            Product::build()->whereIn('uuid', $uuid)->where('is_deleted', 1)->update($request);
            AdminLog::build()->add($userInfo['uuid'], '商品管理-商品推荐', '设置推荐');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static function setCategory($request, $userInfo, $uuid)
    {
        try {
            Product::build()->whereIn('uuid', $uuid)->where('is_deleted', 1)->update($request);
            AdminLog::build()->add($userInfo['uuid'], '商品管理-商品库', '设置分类');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function setOrderNumber($request, $userInfo)
    {
        try{
            $data = Product::build()->where('uuid', $request['uuid'])->where('is_deleted',1)->where('recommend',1)->findOrFail();
            if($request['type'] == 1){
                //上移 order_number+1
                $order_number = $data['order_number']+1;
            }else{
                if($data['order_number'] == 1){
                    return true;
                }
                //下移 order_number-1
                $order_number = $data['order_number'] - 1;
            }
            $res = Product::build()->where('is_deleted',1)->where('order_number',$order_number)->where('recommend',1)->find();
            if(!$res){
                return true;
            }
            Product::build()->where('uuid', $request['uuid'])->where('recommend',1)->update(['order_number' => $order_number]);
            Product::build()->where('uuid', $res['uuid'])->where('recommend',1)->update(['order_number' => $data['order_number']]);
            return true;
        }catch (Exception $e){
            throw new Exception($e->getMessage(), 500);
        }
    }

}
