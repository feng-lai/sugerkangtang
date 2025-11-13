<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\Order;
use app\api\model\Feel;
use think\Exception;
use think\Db;

/**
 * 心得-逻辑
 */
class FeelLogic
{
    static public function cmsList($request, $userInfo)
    {
        $map['o.is_deleted'] = ['=', 1];
        $map['o.status'] = ['=', 1];
        $request['user_uuid'] ? $map['o.user_uuid'] = ['=', $request['user_uuid']] : '';
        $request['admin_uuid'] ? $map['a.uuid'] = ['=', $request['admin_uuid']] : '';
        $request['course_name'] ? $map['c.name'] = ['like', '%'.$request['course_name'].'%'] : '';
        $result = Order::build()
            ->alias('o')
            ->field('
                f.uuid,
                c.name,
                a.name as admin_name,
                ca.name as cate_name,
                o.user_uuid,
                c.admin_uuid,
                o.course_uuid,
                (select status from feel where order_uuid = o.uuid order by create_time desc limit 1) as status,
                (select count(1) as c from feel where order_uuid = o.uuid and status in (0,1) order by create_time desc limit 1) as is_feel,
                (select create_time from feel where order_uuid = o.uuid order by create_time desc limit 1) as create_time,
                f.content,
                f.reason
            ')
            ->join('course c', 'c.uuid = o.course_uuid', 'LEFT')
            ->join('admin a', 'a.uuid = c.admin_uuid', 'LEFT')
            ->join('cate ca', 'ca.uuid = c.cate_uuid', 'LEFT')
            ->join('feel f', 'f.order_uuid = o.uuid', 'LEFT')
            ->where($map)
            ->order('o.create_time desc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
        AdminLog::build()->add($userInfo['uuid'], '心得管理', '查询列表');
        return $result;
    }
    static public function cmsEdit($request, $userInfo){
        $feel = Feel::build()->where('uuid',$request['uuid'])->findOrFail();
        $feel->save($request);
        $text = '已通过';
        if($request['status'] == 2){
            $text = '不通过';
        }
        AdminLog::build()->add($userInfo['uuid'], '心得管理', '审核'.$text,$request);
        return true;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = Feel::build()
            ->field('
                f.uuid,
                u.name as user_name,
                u.avatar,
                u.major,
                u.grade,
                co.name as college_name,
                c.name as course_name,
                a.name as admin_name,
                f.create_time,
                f.content,
                f.img,
                f.user_uuid,
                f.status,
                f.reason,
                o.course_uuid
            ')
            ->alias('f')
            ->where('f.uuid', $id)
            ->join('order o','o.uuid = f.order_uuid')
            ->join('course c','c.uuid = o.course_uuid')
            ->join('user u','u.uuid = o.user_uuid')
            ->join('college co','co.uuid = u.college_uuid')
            ->join('admin a','a.uuid = c.admin_uuid' )
            ->where('f.is_deleted', 1)
            ->findOrFail();
        AdminLog::build()->add($userInfo['uuid'], '心得管理', '查询详情', $id);
        return $data;
    }
}
