<?php

namespace app\api\logic\cms;

use app\api\model\Category;
use app\api\model\AdminLog;
use app\api\model\Product;
use think\Exception;
use think\Db;

/**
 *分类逻辑
 */
class CategoryLogic
{
    static public function cmsList($request, $userInfo)
    {
        $where = ['is_deleted' => 1];
        if ($request['name']) {
            $where['name'] = ['like', '%' . $request['name'] . '%'];
        }
        if ($request['vis']) {
            $where['vis'] = ['=', $request['vis']];
        }
        if ($request['site_id']) {
            $where['site_id'] = ['=', $request['site_id']];
        }
       $result = Category::where($where)->order('order_number desc')->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
        AdminLog::build()->add($userInfo['uuid'], '商品管理-商品分类', '分类列表');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = Category::build()->where('uuid', $id)->where('is_deleted', 1)->findOrFail();
        AdminLog::build()->add($userInfo['uuid'], '商品管理-商品分类', '分类详情');
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            Db::startTrans();
            if(Category::build()->where('is_deleted',1)->where('name',$request['name'])->count()){
                return ['msg'=>'当前名称已存在，请重新输入'];
            }
            $number = Category::build()->where('is_deleted',1)->order('order_number', 'desc')->value('order_number');
            $request['order_number'] = $number?$number+1:'1';
            $request['create_time'] = now_time(time());
            $request['update_time'] = now_time(time());
            $request['uuid'] = uuid();
            Category::build()->insert($request);
            Db::commit();
            AdminLog::build()->add($userInfo['uuid'], '商品管理-商品分类', '新增分类-'.$request['name']);
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
            $data = Category::build()->where('uuid', $request['uuid'])->where('is_deleted',1)->findOrFail();
            $data->save($request);
            Db::commit();
            AdminLog::build()->add($userInfo['uuid'], '商品管理-商品分类', '编辑分类-'.$request['name']);
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
            if(Product::build()->where(['is_deleted'=>1,'category_uuid'=>$id])->count()){
                return ['msg'=>'失败，有商品使用了此分类，请先删除对应商品'];
            }
            $data  = Category::build()->where('uuid', $id)->where('is_deleted',1)->findOrFail();
            $data->save(['is_deleted' => 2]);
            AdminLog::build()->add($userInfo['uuid'], '商品管理-商品分类', '删除分类-'.$data->name);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static function setVis($request, $userInfo, $uuid)
    {
        try {
            $user = Category::build()->where('uuid', $uuid)->where('is_deleted',1)->findOrFail();
            $user->save($request);
            AdminLog::build()->add($userInfo['uuid'], '商品管理-商品分类', '设置状态');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function setOrderNumber($request, $userInfo)
    {
        try{
            $data = Category::build()->where('uuid', $request['uuid'])->where('is_deleted',1)->findOrFail();
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
            $res = Category::build()->where('is_deleted',1)->where('order_number',$order_number)->find();
            if(!$res){
                return true;
            }
            Category::build()->where('uuid', $request['uuid'])->update(['order_number' => $order_number]);
            Category::build()->where('uuid', $res['uuid'])->update(['order_number' => $data['order_number']]);
            AdminLog::build()->add($userInfo['uuid'], '商品管理-商品分类', '上移/下移');
            return true;
        }catch (Exception $e){
            throw new Exception($e->getMessage(), 500);
        }
    }
}
