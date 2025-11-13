<?php

namespace app\api\logic\mini;

use app\api\model\Course;
use app\api\model\Footprint;
use think\Exception;
use think\Db;

/**
 * 浏览记录-逻辑
 */
class FootprintLogic
{
    static public function List($request,$userInfo)
    {
        try {
            $where = ['f.is_deleted' => 1,'user_uuid'=>$userInfo['uuid'],'vis'=>1];
            $result = Footprint::build()
                ->field('f.course_uuid,c.name,c.img,c.status,c.vis')
                ->alias('f')
                ->join('course c','c.uuid = f.course_uuid','left')
                ->where($where)
                ->group('f.course_uuid')
                ->order('f.create_time desc')
                ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
            return $result;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Detail($uuid, $userInfo)
    {
        try {
            $data = Course::build()->where('uuid', $uuid)->where('is_deleted', 1)->findOrFail();
            $data->num = Order::build()->where('course_uuid', $uuid)->where('status', 1)->count();
            $data->admin = Admin::build()->where('uuid', $data->admin_uuid)->value('name');
            //是否收藏
            $data->isCollect = 0;
            $data->feel_uuid = '';
            if ($userInfo) {
                $data->isCollect = Collect::build()->where('user_uuid', $userInfo['uuid'])->where('course_uuid', $uuid)->where('is_deleted', 1)->count();
                //心得uuid
                $data->feel_uuid = Feel::build()
                    ->alias('f')
                    ->join('order o','o.uuid = f.order_uuid')
                    ->where('f.user_uuid',$userInfo['uuid'])
                    ->where('o.course_uuid', $uuid)
                    ->where('f.is_deleted', 1)->value('f.uuid');
            }


            return $data;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
