<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\Feel;
use app\api\model\Order;
use think\Exception;
use think\Db;

/**
 * 心得-逻辑
 */
class CourseFeelLogic
{
    static public function cmsList($request, $userInfo)
    {
        $map['f.is_deleted'] = ['=', 1];
        $request['course_uuid'] ? $map['o.course_uuid'] = ['=', $request['course_uuid']] : '';
        is_numeric($request['status']) ? $map['f.status'] = ['=', $request['status']] : '';
        $request['user_uuid'] ? $map['f.user_uuid'] = ['=', $request['user_uuid']] : '';
        $result = Feel::build()
            ->alias('f')
            ->field('
                f.uuid,
                u.avatar,
                u.name,
                u.number,
                c.name as college_name,
                u.grade,
                u.class,
                f.status,
                f.reason,
                f.content,
                f.img
            ')
            ->join('user u', 'u.uuid = f.user_uuid', 'LEFT')
            ->join('college c', 'c.uuid = u.college_uuid', 'LEFT')
            ->join('order o','o.uuid = f.order_uuid','left')
            ->where($map)
            ->order('o.create_time desc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
        AdminLog::build()->add($userInfo['uuid'], '心得管理', '查询列表');
        return $result;
    }
    static public function cmsEdit($request, $userInfo){
        $text = '已通过';
        if($request['status'] == 2){
            $text = '不通过';
        }
        foreach(explode(',',$request['uuid']) as $v){
            $feel = Feel::build()->where('uuid',$v)->findOrFail();
            if($feel->status != 0){
                return ['msg'=>'非待审核状态'];
            }
            $feel->save(['status'=>$request['status'],'reason'=>$request['reason']]);
            $course = Order::build()->alias('o')->where('o.uuid',$feel->order_uuid)->join('course c','c.uuid = o.course_uuid')->value('c.name');
            send_msg($feel->user_uuid,'',$feel->uuid,'您的课程'.$course.'，学习心得审核'.$text.'，请
及时查看','');
        }
        AdminLog::build()->add($userInfo['uuid'], '心得管理', '审核'.$text,$request);
        return true;
    }
}
