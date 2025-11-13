<?php

namespace app\api\logic\mini;

use app\api\model\Help;
use app\api\model\HelpCategory;
use think\Exception;
use think\Db;

/**
 * 帮助手册-逻辑
 */
class HelpLogic
{
    static public function List($request)
    {
        try {
            $where = ['is_deleted' => 1];
            if ($request['site_id']) {
                $where['site_id'] = $request['site_id'];
            }
            $result = HelpCategory::build()
                ->field('
                    uuid,
                    name
                ')
                ->where($where)
                ->order('create_time desc')
                ->select()->each(function ($item) use ($request,$where) {
                    $where['help_category_uuid'] = $item['uuid'];
                    $where['status'] = 1;
                    $item['help_info'] = Help::build()->field('uuid,name')->where($where)->order('create_time desc')->select();
                });
            return $result;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Detail($uuid, $userInfo)
    {
        try {
            $data = Help::build()->field('uuid,name,desc,create_time')->where('uuid', $uuid)->where('is_deleted', 1)->findOrFail();
            return $data;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
