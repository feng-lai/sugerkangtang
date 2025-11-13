<?php

namespace app\api\logic\mini;

use app\api\model\Collect;
use think\Exception;
use think\Db;

/**
 * 收藏-逻辑
 * User:
 * Date: 2022-07-21
 * Time: 14:31
 */
class CollectLogic
{
    static public function Add($request, $userInfo)
    {
        try {
            //重复收藏
            if (Collect::build()->where('user_uuid', $userInfo['uuid'])->where('product_uuid', $request['product_uuid'])->where('is_deleted', 1)->count()) {
                return ['msg' => '重复收藏'];
            }
            $order = Collect::build();
            $order->uuid = uuid();
            $order->user_uuid = $userInfo['uuid'];
            $order->product_uuid = $request['product_uuid'];
            $order->save();
            return $order->uuid;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function List($request,$userInfo)
    {
        try {
            $where = ['c.is_deleted' => 1,'c.user_uuid'=>$userInfo['uuid']];
            $request['site_id']?$where['c.site_id'] = $request['site_id']:'';
            $result = Collect::build()
                ->field('c.uuid,o.name,c.product_uuid,o.vis,o.main_img,o.price,o.original_price,o.is_original_price,o.selling_point,c.create_time')
                ->alias('c')
                ->join('product o','c.product_uuid = o.uuid','LEFT')
                ->where($where)
                ->order('c.create_time desc')
                ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
            return $result;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Delete($id, $userInfo)
    {
        try {
            Collect::build()->whereIn('product_uuid', $id)->where('user_uuid',$userInfo['uuid'])->where('is_deleted',1)->update(['is_deleted'=>2]);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
