<?php

namespace app\api\logic\cms;

use app\api\model\Admin;
use app\api\model\AdminLog;
use app\api\model\Reason;
use think\Exception;
use think\Db;

/**
 * 原因-逻辑
 */
class ReasonLogic
{
    static public function menu()
    {
        return '订单管理-订单设置';
    }
    static public function cmsList($request,$userInfo)
    {
        $where['site_id'] = $request['site_id'];
        $where['is_deleted'] = 1;
        $request['keyword']?$where['content'] = ['like','%'.$request['keyword'].'%']:'';
        $request['type']?$where['type'] = $request['type']:'';
        $result = Reason::build()
            ->field('*')
            ->where($where)
            ->order('order_number desc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function ($item) {
                $item->admin_name = Admin::build()->where('uuid', '=', $item->admin_uuid)->value('name');
            });
        AdminLog::build()->add($userInfo['uuid'], self::menu(), '查询列表');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = Reason::build()
            ->where('uuid', $id)
            ->where('is_deleted', 1)
            ->findOrFail();
        AdminLog::build()->add($userInfo['uuid'],  self::menu(), '查询详情：' . $data->content);
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            $number = Reason::build()->where('is_deleted',1)->where('type',$request['type'])->order('order_number', 'desc')->value('order_number');
            $request['uuid'] = uuid();
            $request['create_time'] = now_time(time());
            $request['update_time'] = now_time(time());
            $request['admin_uuid'] = $userInfo['uuid'];
            $request['order_number'] = $number?$number+1:'1';
            Reason::build()->save($request);
            AdminLog::build()->add($userInfo['uuid'],  self::menu(), '新增：' . $request['content']);
            return $request['uuid'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo)
    {
        try {
            $data = Reason::build()->where('uuid', $request['uuid'])->where('is_deleted',1)->findOrFail();
            $data->save($request);
            AdminLog::build()->add($userInfo['uuid'],  self::menu(), '更新：' . $data->content);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id, $userInfo)
    {
        try {
            Reason::build()->whereIn('uuid', $id)->update(['is_deleted' => 2]);
            AdminLog::build()->add($userInfo['uuid'],  self::menu(), '删除');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function setOrderNumber($request, $userInfo)
    {
        try{
            $data = Reason::build()->where('uuid', $request['uuid'])->where('is_deleted',1)->findOrFail();
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
            $res = Reason::build()->where('is_deleted',1)->where('order_number',$order_number)->where('type',$data->type)->find();
            if(!$res){
                return true;
            }
            Reason::build()->where('uuid', $request['uuid'])->update(['order_number' => $order_number]);
            Reason::build()->where('uuid', $res['uuid'])->update(['order_number' => $data['order_number']]);
            return true;
        }catch (Exception $e){
            throw new Exception($e->getMessage(), 500);
        }
    }
}
