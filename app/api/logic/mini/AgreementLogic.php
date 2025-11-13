<?php

namespace app\api\logic\mini;

use app\api\model\Agreement;
use think\Exception;
use think\Db;

/**
 * 协议中心-逻辑
 * User: Yacon
 * Date: 2022-07-21
 * Time: 14:31
 */
class AgreementLogic
{
    static public function List($request)
    {
        try {
            $where = [
                'is_deleted'=>1,
                'site_id'=>$request['site_id'],
            ];
            $request['type']?$where['type'] = $request['type']:'';
            return Agreement::build()->where($where)->order('ver desc')->select();
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Detail($uuid){
        try {
            return Agreement::build()->where('is_deleted', 1)->where('uuid',$uuid)->findOrFail();
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }


}
