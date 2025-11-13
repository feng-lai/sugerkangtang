<?php

namespace app\api\logic\mini;

use app\api\model\Cate;
use app\api\model\College;
use app\api\model\Course;
use app\api\model\Order;
use app\api\model\Admin;
use app\api\model\Collect;
use app\api\model\Feel;
use app\api\model\Footprint;
use think\Exception;
use think\Db;

/**
 * 拼团课程-逻辑
 */
class CourseLogic
{
    static public function List($request)
    {
        try {
            $where = ['is_deleted' => 1, 'vis' => 1];
            if ($request['cate_uuid']) {
                $where['cate_uuid'] = $request['cate_uuid'];
            }
            if ($request['status']) {
                $where['status'] = $request['status'];
            }
            if ($request['active_time']) {
                $where['begin'] = ['<=', $request['active_time']];
                $where['end'] = ['>=', $request['active_time'] . ' 23:59:59'];
            }
            if ($request['name']) {
                $where['name'] = ['like', '%' . $request['name'] . '%'];
            }
            $result = Course::build()->where($where);
            if ($request['tag']) {
                $result = $result->where("FIND_IN_SET('" . $request['tag'] . "', tag)");
            }
            if ($request['college_uuid']) {
                $result = $result->where(function ($query) use ($request) {
                    $query->whereNull('college')->whereOr('college','')->whereOr("FIND_IN_SET('" . $request['college_uuid'] . "', college)");
                });
                //$result = $result->where("FIND_IN_SET('".$request['college_uuid']."', college)");
            }

            $result = $result->order('create_time desc')->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
            foreach ($result as $v) {
                $v->num = Order::build()->where('course_uuid', $v->uuid)->where('status', 1)->count();
            }
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
            $data->cate_name = Cate::build()->where('uuid', $data->cate_uuid)->value('name');
            $data->college_name = College::build()->where('uuid','in', explode(',',$data->college))->column('name');
            //是否收藏
            $data->isCollect = 0;
            //是否报名
            $data->isOrder = 0;
            $data->feel_uuid = '';
            if ($userInfo) {
                $data->isCollect = Collect::build()->where('user_uuid', $userInfo['uuid'])->where('course_uuid', $uuid)->where('is_deleted', 1)->count();
                $data->isOrder = Order::build()->where('user_uuid', $userInfo['uuid'])->where('course_uuid', $uuid)->where('is_deleted', 1)->where('status',1)->count();
                //心得uuid
                $data->feel_uuid = Feel::build()
                    ->alias('f')
                    ->join('order o','o.uuid = f.order_uuid')
                    ->where('f.user_uuid',$userInfo['uuid'])
                    ->where('o.course_uuid', $uuid)
                    ->where('f.is_deleted', 1)->value('f.uuid');
                //浏览记录
                if(!Footprint::build()->whereTime('create_time', 'today')->where('user_uuid',$userInfo['uuid'])->where('course_uuid',$uuid)->count()){
                    $footprint = new Footprint();
                    $footprint->user_uuid = $userInfo['uuid'];
                    $footprint->uuid = uuid();
                    $footprint->course_uuid = $uuid;
                    $footprint->save();
                }

            }


            return $data;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
