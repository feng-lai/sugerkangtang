<?php

namespace app\api\logic\mini;

use app\api\model\Feel;
use app\api\model\Order;
use app\api\model\Course;
use app\api\model\User;
use think\Exception;
use think\Db;

/**
 * 心得-逻辑
 */
class FeelLogic
{
    static public function List($request)
    {
        try {
            $where = ['f.is_deleted' => 1];
            if($request['order_uuid']){
                $where['f.order_uuid'] = $request['order_uuid'];
            }
            if($request['course_uuid']){
                $where['o.course_uuid'] = $request['course_uuid'];
            }
            $result = Feel::build()
                ->alias('f')
                ->field('o.course_uuid,f.uuid,f.img,f.content,f.anonymous,f.order_uuid,f.status,f.anonymous,f.create_time,f.update_time,u.name,u.avatar')
                ->join('order o','o.uuid = f.order_uuid')
                ->join('course c','c.uuid = o.course_uuid')
                ->join('user u','u.uuid = f.user_uuid')
                ->where($where)
                ->order('f.create_time desc')
                ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
            return $result;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Add($request, $userInfo)
    {
        try {
            //拉黑用户无法发布心得
            if($userInfo['disabled'] == 2){
                return ['msg'=>'由于多次拼团违约或评论失范，用户已被拉黑，无法发布心得，可联系辅导员解封'];
            }
            $order = Order::build()->where('uuid',$request['order_uuid'])->where('user_uuid',$userInfo['uuid'])->where('is_deleted',1)->findOrFail();
            $course = Course::build()->where('uuid',$order->course_uuid)->findOrFail();
            if($order->status == 2){
                return ['msg'=>'拼课已取消'];
            }
            if($course->status != 3){
                return ['msg'=>'课程还没结束'];
            }
            if(Feel::build()->where('user_uuid',$userInfo['uuid'])->where('is_deleted',1)->where('order_uuid',$request['order_uuid'])->where('status',0)->count()){
                return ['msg'=>'重复提交'];
            }
            $feel = Feel::build();
            $feel->uuid = uuid();
            $feel->user_uuid = $userInfo['uuid'];
            $feel->order_uuid = $request['order_uuid'];
            $feel->img = $request['img'];
            $feel->content = $request['content'];
            $feel->anonymous = $request['anonymous'];
            $feel->save();
            return $feel->uuid;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Detail($id)
    {
        try {
            $data = Feel::build()
                ->alias('f')
                ->field('f.uuid,f.user_uuid,f.img,f.content,f.anonymous,f.order_uuid,f.status,f.anonymous,f.create_time,f.update_time,u.name,u.avatar')
                ->join('user u','u.uuid = f.user_uuid')
                ->where('f.uuid', $id)
                ->where('f.is_deleted', 1)
                ->findOrFail();
            return $data;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }


}
