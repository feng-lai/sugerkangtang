<?php

namespace app\api\logic\cms;

use app\api\model\Category;
use app\api\model\Parameter;
use app\api\model\AdminLog;
use app\api\model\Product;
use app\api\model\ProductParameter;
use think\Exception;
use think\Db;

/**
 *商品参数逻辑
 */
class ParameterLogic
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
        if ($request['type']) {
            $where['type'] = ['=', $request['type']];
        }
        if ($request['site_id']) {
            $where['site_id'] = ['=', $request['site_id']];
        }
       $result = Parameter::where($where)
           ->order('create_time desc')
           ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])
           ->each(function ($item) {
               $item->category_name = Category::build()->where('uuid',$item->category_uuid)->value('name');
               if($item['type'] == 1){
                   $item->num = ProductParameter::build()->where('parameter_uuid',$item->uuid)->where('is_deleted',1)->count();
               }
               if($item['type'] == 2){
                   $item->num = Product::build()->where('effect','like','%'.$item->uuid.'%')->where('is_deleted',1)->count();
               }
               if($item['type'] == 3){
                   $item->num = Product::build()->where('suitable_for','like','%'.$item->uuid.'%')->where('is_deleted',1)->count();
               }
               if($item['type'] == 4){
                   $item->num = Product::build()->where('product_type',$item->uuid)->where('is_deleted',1)->count();
               }

           });
        AdminLog::build()->add($userInfo['uuid'], '商品管理-参数库', '参数库列表');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = Parameter::build()->where('uuid', $id)->where('is_deleted', 1)->findOrFail();
        AdminLog::build()->add($userInfo['uuid'], '商品管理-参数库', '参数库详情');
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            Db::startTrans();
            if(Parameter::build()->where('is_deleted',1)->where('name',$request['name'])->count()){
                return ['msg'=>'当前名称已存在，请重新输入'];
            }
            $request['create_time'] = now_time(time());
            $request['update_time'] = now_time(time());
            $request['uuid'] = uuid();
            Parameter::build()->insert($request);
            Db::commit();
            AdminLog::build()->add($userInfo['uuid'], '商品管理-参数库', '新增参数-'.$request['name']);
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
            if(!$request['category_uuid']) {
                unset($request['category_uuid']);
            }
            $data = Parameter::build()->where('uuid', $request['uuid'])->where('is_deleted',1)->findOrFail();
            $data->save($request);
            Db::commit();
            AdminLog::build()->add($userInfo['uuid'], '商品管理-参数库', '编辑参数-'.$request['name']);
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
            if(ProductParameter::build()->where(['is_deleted'=>1,'parameter_uuid'=>$id])->count()){
                return ['msg'=>'失败，有商品使用了此参数，请先删除对应商品'];
            }
            $data  = Parameter::build()->where('uuid', $id)->where('is_deleted',1)->findOrFail();
            $data->save(['is_deleted' => 2]);
            AdminLog::build()->add($userInfo['uuid'], '商品管理-参数库', '删除参数-'.$data->name);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static function setStatus($request, $userInfo, $uuid)
    {
        try {
            $user = Parameter::build()->where('uuid', $uuid)->where('is_deleted',1)->findOrFail();
            $user->save($request);
            AdminLog::build()->add($userInfo['uuid'], '商品管理-参数库', '设置状态');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }


}
