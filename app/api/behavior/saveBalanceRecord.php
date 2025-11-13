<?php
/**
 * Created by Terry.
 * User: Terry
 * Email: terr_exchange@outlook.com
 * Date: 2020/9/10
 * Time: 16:16
 */

namespace app\api\behavior;


use app\api\model\UserBalanceRecord;
use app\api\model\UserMain;

//余额变动事件
class saveBalanceRecord
{
    /**
     * @param $params
     * $params.type int 1收入 2支出
     * $params.record_type int 1商品订单 2拍品订单
     * $params.user object 当前用户对象
     * $params.amount float 变动的余额
     * $params.comment string 变动原因
     * $params.withdraw_uuid string 提现的uuid
     */
    public function run(&$params)
    {





    }

}