<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\Order;
use app\api\model\Sign;
use app\api\model\User;
use app\api\model\Course;
use think\Exception;
use think\Db;

/**
 * 签到-逻辑
 * User:
 * Date: 2022-08-11
 * Time: 21:24
 */
class SignLogic
{
    static public function cmsList($request,$userInfo)
    {
        $map['o.status'] = ['=', 1];
        $map['o.is_deleted'] = ['=', 1];
        $request['user_uuid'] ? $map['o.user_uuid'] = ['=', $request['user_uuid']] : '';
        $result = Order::build()
            ->field('c.name,o.create_time,a.name as admin_name,ca.name as cate_name,c.class_begin,o.user_uuid,(select count(1) as status from sign where user_uuid = o.user_uuid and course_uuid = o.course_uuid) as is_sign')
            ->alias('o')
            ->join('course c', 'c.uuid = o.course_uuid', 'LEFT')
            ->join('admin a', 'a.uuid = c.admin_uuid', 'LEFT')
            ->join('cate ca', 'ca.uuid = c.cate_uuid', 'LEFT')
            ->where($map)
            ->order('o.create_time desc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
        AdminLog::build()->add($userInfo['uuid'], '报名管理', '查询列表');
        return $result;
    }
    static public function cmsDelete($id, $userInfo)
    {
        try {
            $order = Order::build()->where('uuid',$id)->where('is_deleted',1)->findOrFail();
            Sign::build()->where('user_uuid', $order->user_uuid)->where('course_uuid',$order->course_uuid)->where('is_deleted',1)->update(['is_deleted' => 2]);
            AdminLog::build()->add($userInfo['uuid'], '签到管理', '取消：' . $id, $id);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
    static public function cmsAdd($request, $userInfo)
    {
        try {
            $user = User::build()->where('uuid',$request['user_uuid'])->findOrFail();
            $course = Course::build()->where('uuid',$request['course_uuid'])->findOrFail();
            $order = Order::build()->where('user_uuid',$request['user_uuid'])->where('course_uuid',$request['course_uuid'])->findOrFail();
            $sign = new Sign();
            $sign->uuid = uuid();
            $sign->user_uuid = $request['user_uuid'];
            $sign->course_uuid = $request['course_uuid'];
            $sign->save();
            return $sign->uuid;
            AdminLog::build()->add($userInfo['uuid'], '签到管理', '设置为已签到', $request);
            return $data['uuid'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
