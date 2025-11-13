<?php

namespace app\api\logic\mini;

use app\api\model\Evaluate;
use app\api\model\Sign;
use app\api\model\Order;
use think\Exception;

/**
 * 用户拼课-逻辑
 */
class UserCourseLogic
{
    static public function miniList($request, $userInfo)
    {
        try {
            $where['o.user_uuid'] = $userInfo['uuid'];
            if ($request['status']) {
                if ($request['status'] == 4) {
                    $where['o.status'] = 2;
                } else {
                    $where['c.status'] = $request['status'];
                    $where['o.status'] = ['<>', 2];
                }
            }
            $result = Order::build()
                ->alias('o')
                ->field('o.uuid,o.course_uuid,o.status as order_status,c.status,c.name,c.img,c.tag,c.address,c.class_begin')
                ->join('course c', 'c.uuid = o.course_uuid', 'left')
                ->where($where)
                ->order('c.class_begin DESC')
                ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
            foreach ($result as $v) {
                if ($v->order_status == 2) {
                    $v->status = 4;
                }
                unset($v->order_status);
                $v->is_sign = Sign::build()->where('is_deleted', 1)->where('user_uuid', $userInfo['uuid'])->where('course_uuid', $v->course_uuid)->count();
            }
            return $result;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function miniDetail($uuid, $userInfo)
    {
        try {
            $result = Order::build()->alias('o')
                ->field('
                    o.uuid,
                    o.course_uuid,
                    o.status as order_status,
                    o.cancel_time,
                    o.reason,
                    c.admin_uuid,
                    c.status,
                    c.name,
                    c.img,
                    c.tag,
                    c.address,
                    c.class_begin,
                    a.name as admin_name,
                    c.dsc,
                    c.min,
                    c.max,
                    o.create_time,
                    c.begin,
                    s.create_time as sign_time
                ')
                ->join('course c', 'c.uuid = o.course_uuid', 'left')
                ->join('admin a', 'a.uuid = c.admin_uuid', 'left')
                ->join('sign s', 's.course_uuid = o.course_uuid and s.user_uuid = o.user_uuid', 'left')
                ->where('o.uuid', $uuid)
                ->find();
            if($result->order_status == 2){
                $result->status = 4;
            }
            unset($result->order_status);
            //当前报名人数
            $result->order_num = Order::build()->where('course_uuid',$result->course_uuid)->where('status',1)->count();
            //成团剩余人数
            $result->left_num = max(0,$result->min - $result->order_num);
            //评价uuid
            $evaluate_uuid = Evaluate::build()->where(['user_uuid'=>$userInfo['uuid'],'course_uuid'=>$result['course_uuid'],'admin_uuid'=>$result['admin_uuid']])->value('uuid');
            $result->evaluate_uuid = $evaluate_uuid?$evaluate_uuid:'';
            return $result;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
