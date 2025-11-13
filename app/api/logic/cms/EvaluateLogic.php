<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\Evaluate;
use think\Exception;
use think\Db;

/**
 * 评价逻辑
 */
class EvaluateLogic
{
    static public function cmsList($request, $userInfo)
    {
        $where['e.is_deleted'] = 1;
        if($request['admin_uuid']){
            $where['e.admin_uuid'] = $request['admin_uuid'];
        }
        $result = Evaluate::build()
            ->alias('e')
            ->field('e.uuid,c.name,u.name as uname,e.anonymous,e.point,e.create_time,e.content,e.img')
            ->join('course c','c.uuid = e.course_uuid')
            ->join('user u','u.uuid = e.user_uuid')
            ->where($where)
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);;

        AdminLog::build()->add($userInfo['uuid'], '评价', '查询列表', $request);
        return $result;
    }
    static public function cmsDetail($id, $userInfo){
        $data = Evaluate::build()
            ->alias('e')
            ->field('u.name,u.avatar,u.number,c.name as college_name,e.anonymous,e.point,e.content,e.img')
            ->join('user u','u.uuid = e.user_uuid')
            ->join('college c','c.uuid = u.college_uuid')
            ->where('e.uuid', $id)
            ->where('e.is_deleted', 1)
            ->findOrFail();
        AdminLog::build()->add($userInfo['uuid'], '评价', '查询详情', $id);
        return $data;
    }
}
