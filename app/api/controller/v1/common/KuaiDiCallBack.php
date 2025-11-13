<?php

namespace app\api\controller\v1\common;

use app\api\controller\Api;
use app\api\model\Order;
use app\api\model\OrderPath;
use think\Exception;

/**
 * -控制器
 */
class KuaiDiCallBack extends Api
{
    public $restMethodList = 'post';

    public function save()
    {
        $data = json_decode($_POST['param'],true);
        $info = json_decode($_POST['param'],true)['lastResult']['data'];
        $num = $data['lastResult']['nu'];
        if(!$info){
            return json_encode(['result'=>true,'returnCode'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
        }
        $order_id = Order::build()->where('num',$num)->select();
        foreach ($order_id as $v){
            OrderPath::build()->where('order_id',$v->order_id)->delete();
            foreach ($info as $val){
                OrderPath::build()->insert([
                    'uuid'=>uuid(),
                    'order_id'=>$v->order_id,
                    'time' => $val['time'],
                    'context'=>$val['context'],
                    'status'=>$val['status'],
                    'create_time'=>now_time(time()),
                    'update_time'=>now_time(time()),
                ]);
            }
        }

        return json_encode(['result'=>true,'returnCode'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
    }
}
