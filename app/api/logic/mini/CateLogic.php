<?php

namespace app\api\logic\mini;

use app\api\model\Cate;
use think\Exception;
use think\Db;

/**
 * 分类-逻辑
 */
class CateLogic
{
    static public function List($request)
    {
        $where = ['is_deleted' => 1, 'vis' => 1];
        if($request['home_vis']){
            $where['home_vis'] = $request['home_vis'];
        }
        if($request['level']){
            $where['level'] = $request['level'];
        }
        if($request['pid']){
            $where['pid'] = $request['pid'];
        }
        $result = Cate::build()
            ->where($where)
            ->order('sort asc')
            ->order('update_time desc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
        return $result;
    }
}
