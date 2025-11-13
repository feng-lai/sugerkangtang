<?php

namespace app\api\model;

use app\common\tools\kuaidi\Kuaidi;
/**
 * 订单物流轨迹-模型
 */
class OrderPath extends BaseModel
{
    public static function build()
    {
        return new self();
    }

    public function getPath($data)
    {
        $status = $this::build()->where('order_id', $data['order_id'])->order('time desc')->value('status');
        if ($status == '签收' && $status) {
            return true;
        }
        $res = new Kuaidi();
        $res->subscribe($data['com'],$data['num']);
        $info = $res->synquery($data['com'], $data['num']);
        if($info['message'] == 'ok'){
            $this::build()->where('order_id', $data['order_id'])->delete();
            foreach ($info['data'] as $key => $value) {
                $this::build()->insert([
                    'uuid' => uuid(),
                    'order_id' => $data['order_id'],
                    'time' => $value['time'],
                    'context' => $value['context'],
                    'status' => $value['status'],
                    'create_time' => now_time(time()),
                    'update_time' => now_time(time()),
                ]);
            }
        }

    }



}
