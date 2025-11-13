<?php

namespace app\api\logic\cms;

use app\api\model\Admin;
use app\api\model\AdminLog;
use app\api\model\Expert;
use think\Exception;
use think\Db;

/**
 * 字典逻辑
 */
class ExpertLogic
{
    static public function getMenu()
    {
        return '内容管理-平台内容配置-专家资料';
    }

    static public function cmsList($request, $userInfo)
    {
        $result = Expert::build();
        if ($request['site_id']) $result = $result->where('site_id', '=', $request['site_id']);
        $result = $result
            ->where('is_deleted', 1)
            ->order('order_number desc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function ($item) {
                $item->admin_name = Admin::build()->where('uuid',$item->admin_uuid)->value('name');
            });
        AdminLog::build()->add($userInfo['uuid'], self::getMenu(), '查看列表');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = Expert::build()->where('uuid', $id)->where('is_deleted', 1)->findOrFail();
        AdminLog::build()->add($userInfo['uuid'], self::getMenu(), '查看详情');
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            $number = Expert::build()->where('is_deleted',1)->order('order_number', 'desc')->value('order_number');
            $request['uuid'] = uuid();
            $request['admin_uuid'] = $userInfo['uuid'];
            $request['order_number'] = $number?$number+1:'1';
            $request['create_time'] = now_time(time());
            $request['update_time'] = now_time(time());
            Expert::build()->insert($request);
            AdminLog::build()->add($userInfo['uuid'], self::getMenu(), '新增-' . $request['title']);
            return $request['uuid'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo)
    {
        try {
            $user = Expert::build()->where('uuid', $request['uuid'])->findOrFail();
            $user->save($request);
            AdminLog::build()->add($userInfo['uuid'], self::getMenu(), '编辑-' . $user['title']);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id, $userInfo)
    {
        try {
            $data = Expert::build()->whereIn('uuid', $id)->update(['is_deleted' => 2,'update_time' => now_time(time())]);
            AdminLog::build()->add($userInfo['uuid'], self::getMenu(), '删除');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function setStatus($request, $userInfo)
    {
        $banner = Expert::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
        $banner->save(['status' => $request['status']]);
        AdminLog::build()->add($userInfo['uuid'], self::getMenu(), '设置状态');
        return true;
    }

    static public function setOrderNumber($request, $userInfo)
    {
        try{
            $data = Expert::build()->where('uuid', $request['uuid'])->where('is_deleted',1)->findOrFail();
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
            $res = Expert::build()->where('is_deleted',1)->where('order_number',$order_number)->find();
            if(!$res){
                return true;
            }
            Expert::build()->where('uuid', $request['uuid'])->update(['order_number' => $order_number]);
            Expert::build()->where('uuid', $res['uuid'])->update(['order_number' => $data['order_number']]);
            return true;
        }catch (Exception $e){
            throw new Exception($e->getMessage(), 500);
        }
    }
}
