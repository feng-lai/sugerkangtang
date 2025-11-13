<?php

namespace app\api\logic\mini;

use app\api\model\Msg;
use think\Exception;
use think\Db;

/**
 * 消息-逻辑
 */
class MsgLogic
{
    static public function List($request, $userInfo)
    {
        try {
            $where = ['is_deleted' => 1, 'user_uuid' => $userInfo['uuid']];
            if ($request['type']) {
                $where['type'] = $request['type'];
            }
            if ($request['site_id']) {
                $where['site_id'] = $request['site_id'];
            }
            $result = Msg::build()
                ->field('uuid,title,content,is_read,type,order_id,after_sale_id,medical_report_uuid,create_time')
                ->where($where)
                ->order('create_time desc')
                ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
            return $result;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Detail($uuid, $userInfo)
    {
        try {
            $where = ['is_read' => 2, 'is_deleted' => 1, 'user_uuid' => $userInfo['uuid']];
            return [
                'order_num' => Msg::build()->where($where)->where('type', 1)->count(),
                'medical_report_num' => Msg::build()->where($where)->where('type', 2)->count(),
                'after_sale_num' => Msg::build()->where($where)->where('type', 3)->count()
            ];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Edit($uuid, $userInfo)
    {
        try {
            $where = ['is_read' => 2, 'is_deleted' => 1, 'user_uuid' => $userInfo['uuid']];
            $data = Msg::build()->where($where)->findOrFail();
            $data->save(['is_read' => 1,'update_time' => now_time(time())]);
            return true;
        }catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
