<?php

namespace app\api\logic\cms;

use app\api\model\Admin;
use app\api\model\Invoice;
use app\api\model\AdminLog;
use app\api\model\Order;
use app\api\model\OrderDetail;
use app\api\model\User;
use think\Exception;
use think\Db;

/**
 * 发票管理-逻辑
 */
class InvoiceLogic
{
    static public function menu()
    {
        return '财务管理-发票管理';
    }


    static public function List($request, $userInfo)
    {
        try {
            $where = [
                'is_deleted' => 1,
                'site_id' => $request['site_id'],
            ];
            $request['user_uuid'] ? $where['user_uuid'] = $request['user_uuid'] : '';
            $request['status'] ? $where['status'] = $request['status'] : '';
            $request['keyword'] ? $where['order_id|invoice_id'] = $request['keyword'] : '';
            $request['start_time'] ? $where['create_time'] = ['between', [$request['start_time'], $request['end_time']]] : '';
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '查询列表');
            return Invoice::build()->where($where)->order('create_time desc')->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function ($item) {
                $item->admin_name = Admin::build()->where('uuid', $item->admin_uuid)->value('name');
            });
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Edit($request, $userInfo)
    {
        try {
            $data = Invoice::build()->where(['uuid' => $request['uuid']])->findOrFail();
            if($request['status'] == 3){
                $data->save(['status' => 3,'update_time' => now_time(time()),'handle_time' => now_time(time()),'admin_uuid' => $userInfo['uuid']]);
            }else{
                $request['update_time'] = now_time(time());
                $request['handle_time'] = now_time(time());
                $request['admin_uuid'] = $userInfo['uuid'];
                $data->save($request);
            }
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '立即开票');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Detail($uuid, $userInfo)
    {
        try {
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '查询详情');
            $data = Invoice::build()->where('is_deleted', 1)->where('uuid', $uuid)->findOrFail();
            $data->order = Order::build()->field('order_id,create_time')->where('order_id', $data->order_id)->find();
            $data->product = OrderDetail::build()
                ->field('pr.uuid as product_uuid,pr.name,pa.qty,pa.img')
                ->alias('o')
                ->join('product_attribute pa', 'pa.uuid = o.product_attribute_uuid', 'LEFT')
                ->join('product pr', 'pr.uuid = pa.product_uuid', 'LEFT')
                ->where('o.order_id', $data->order_id)
                ->select();
            $data->user = User::build()->field('uuid as user_uuid,name,img,phone')->where('uuid', $data->user_uuid)->find();
            $data->admin_name = Admin::build()->where('uuid', $data->admin_uuid)->value('name');
            return $data;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Delete($uuid, $userInfo)
    {
        try {
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '删除');
            Invoice::build()->whereIn('uuid', explode(',', $uuid))->update(['is_deleted' => 2]);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function setDefault($uuid, $userInfo)
    {
        try {
            $data = Invoice::build()->where('uuid', $uuid)->findOrFail();
            Invoice::build()->where('user_uuid', $data->user_uuid)->update(['is_default' => 2]);
            $data->save(['is_default' => 1]);
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '设置默认收货地址');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

}
