<?php
/**
 * Created by Terry.
 * User: Terry
 * Email: terr_exchange@outlook.com
 * Date: 2020/9/10
 * Time: 21:21
 */

namespace app\api\behavior;


use app\api\model\LangCoinConfig;
use app\api\model\LangCoinLog;
use app\api\model\UserMain;

class saveCoinRecord
{
    /**
     * @param $params
     * $params.quantity int 变动数量 可以为负数
     * $params.activity_name string 充值活动名称
     * $params.user object 当前用户对象
     * $params.type int 变动类型
     */
    public function run(&$params)
    {

    }
}