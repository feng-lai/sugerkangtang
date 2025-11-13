<?php

namespace app\api\logic\cms;

use app\api\model\Attribute;
use app\api\model\AdminLog;
use app\api\model\ProductAttribute;
use think\Exception;
use think\Db;

/**
 *规格库逻辑
 */
class AttributeLogic
{
    static public function cmsList($request, $userInfo)
    {
        $where = ['is_deleted' => 1];
        if ($request['name']) {
            $where['name'] = ['like', '%' . $request['name'] . '%'];
        }
        if ($request['status']) {
            $where['status'] = ['=', $request['status']];
        }
        if ($request['site_id']) {
            $where['site_id'] = ['=', $request['site_id']];
        }
       $result = Attribute::where($where)
           ->order('create_time desc')
           ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])
           ->each(function ($item) {
               $item->num = ProductAttribute::build()->where('attribute_uuid',$item->uuid)->group('product_uuid')->where('is_deleted',1)->count();
           });
        AdminLog::build()->add($userInfo['uuid'], '商品管理-规格库', '规格库列表');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = Attribute::build()->where('uuid', $id)->where('is_deleted', 1)->findOrFail();
        AdminLog::build()->add($userInfo['uuid'], '商品管理-规格库', '规格库详情');
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            Db::startTrans();
            $request['create_time'] = now_time(time());
            $request['update_time'] = now_time(time());
            $request['uuid'] = uuid();
            Attribute::build()->save($request);
            Db::commit();
            AdminLog::build()->add($userInfo['uuid'], '商品管理-规格库', '新增规格-'.$request['name']);
            return $request['uuid'];
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo, $uuid)
    {
        try {
            Db::startTrans();
            $data = Attribute::build()->where('uuid', $uuid)->where('is_deleted',1)->findOrFail();
            $data->save($request);
            Db::commit();
            AdminLog::build()->add($userInfo['uuid'], '商品管理-规格库', '编辑规格-'.$request['name']);
            return true;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id, $userInfo)
    {
        try {
            //判断商品有没有用到
            if(ProductAttribute::build()->where(['is_deleted'=>1,'attribute_uuid'=>$id])->count()){
                return ['msg'=>'失败，有商品使用了此规格，请先删除对应商品'];
            }
            $data  = Attribute::build()->where('uuid', $id)->where('is_deleted',1)->findOrFail();
            $data->save(['is_deleted' => 2]);
            AdminLog::build()->add($userInfo['uuid'], '商品管理-规格库', '删除规格-'.$data->name);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static function setStatus($request, $userInfo, $uuid)
    {
        try {
            $user = Attribute::build()->where('uuid', $uuid)->where('is_deleted',1)->findOrFail();
            $user->save($request);
            AdminLog::build()->add($userInfo['uuid'], '商品管理-参数库', '设置状态');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }


}
