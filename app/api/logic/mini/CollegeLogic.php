<?php

namespace app\api\logic\mini;

use app\api\model\College;
use think\Exception;
use think\Db;

/**
 * 书院-逻辑
 */
class CollegeLogic
{
    static public function List()
    {
        $where = ['is_deleted' => 1];
        $result = College::build()
            ->where($where)
            ->where('name','<>','兜底')
            ->select();
        return $result;
    }
}
