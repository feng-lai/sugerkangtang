<?php

namespace app\api\logic\mini;

use app\api\model\Evaluate;
use app\api\model\Course;
use app\api\model\Order;
use think\Exception;
use think\Db;

/**
 * 评价-逻辑
 */
class EvaluateLogic
{
    static public function Add($request, $userInfo)
    {
        try {
            Course::build()->where('uuid', $request['course_uuid'])->where('admin_uuid',$request['admin_uuid'])->where('is_deleted', 1)->findOrFail();
            Order::build()->where('is_deleted', 1)->where('course_uuid', $request['course_uuid'])->where('user_uuid', $userInfo['uuid'])->findOrFail();
            //重复评价
            if (Evaluate::build()->where('user_uuid', $userInfo['uuid'])->where('course_uuid', $request['course_uuid'])->where('admin_uuid', $request['admin_uuid'])->where('is_deleted', 1)->count()) {
                return ['msg' => '重复评价'];
            }
            $order = Evaluate::build();
            $order->uuid = uuid();
            $order->user_uuid = $userInfo['uuid'];
            $order->admin_uuid = $request['admin_uuid'];
            $order->content = $request['content'];
            $order->point = $request['point'];
            $order->anonymous = $request['anonymous'];
            $order->img = $request['img'];
            $order->course_uuid = $request['course_uuid'];
            $order->save();
            return $order->uuid;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Detail($uuid,$userInfo)
    {
        try {
            $result = Evaluate::build()
                ->field('e.uuid,e.content,e.point,e.img,a.img as admin_img,a.name as admin_name,a.uuid as admin_uuid')
                ->alias('e')
                ->join('admin a','a.uuid = e.admin_uuid','LEFT')
                ->where(['e.is_deleted' => 1,'e.user_uuid'=>$userInfo['uuid'],'e.uuid'=>$uuid])
                ->findOrFail();
            return $result;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Delete($id, $userInfo)
    {
        try {
            $res = Evaluate::build()->where('uuid', $id)->where('user_uuid',$userInfo['uuid'])->where('is_deleted',1)->findOrFail();
            $res->save(['is_deleted'=>2]);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
