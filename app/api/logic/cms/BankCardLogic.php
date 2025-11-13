<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\BankCard;
use think\Exception;

/**
 * 银行卡-逻辑
 */
class BankCardLogic
{
    static public function menu()
    {
        return '推广管理-分销员管理';
    }


    static public function List($request, $userInfo)
    {
        try {
            $where = [
                'is_deleted' => 1,
                'site_id' => $request['site_id'],
            ];
            $request['user_uuid']?$where['user_uuid'] = $request['user_uuid']:'';
            $request['number']?$where['number'] = $request['number']:'';
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '查询银行卡列表');
            return BankCard::build()->where($where)->order('create_time desc')->paginate(['list_rows'=>$request['page_size'],'page'=>$request['page_index']])->toArray();
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

}
