<?php

namespace app\api\logic\mini;

use app\api\model\Sign;
use app\api\model\Course;
use app\api\model\Order;
use think\Exception;
use think\Db;

/**
 * 签到-逻辑
 */
class SignLogic
{
    static public function miniAdd($request, $userInfo)
    {
        try {
            $data = Course::build()->where('uuid',$request['course_uuid'])->findOrFail();
            $order = Order::build()->where('course_uuid',$request['course_uuid'])->where('user_uuid',$userInfo['uuid'])->where('is_deleted',1)->findOrFail();
            //报名结束到下课时间可以进行签到
            if(time()<strtotime($data->end)){
                return ['msg'=>'报名期间内不能签到'];
            }
            if(time()>strtotime($data->class_end)){
                return ['msg'=>'已经下课了，无法签到'];
            }
            //重复签到
            $sign = Sign::build()->where('user_uuid',$userInfo['uuid'])->where('course_uuid',$request['course_uuid'])->where('is_deleted',1)->count();
            if($sign){
                return ['msg'=>'重复签到'];
            }
            $res = Sign::build();
            $res->uuid = uuid();
            $res->user_uuid = $userInfo['uuid'];
            $res->course_uuid = $request['course_uuid'];
            $res->save();
            //发送签到成功通知
            send_msg($userInfo['uuid'],$order->uuid,'','你报名的'.$data->name.'已成功签到',$request['course_uuid']);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }


}
