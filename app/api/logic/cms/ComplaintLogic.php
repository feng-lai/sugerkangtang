<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\Complaint;
use think\Exception;
use think\Db;

/**
 * 需求建议逻辑
 */
class ComplaintLogic
{
    static public function cmsList($request, $userInfo)
    {
        $result = Complaint::build();
        if ($request['type']) $result = $result->where('c.type', '=', $request['type']);
        if ($request['status']) $result = $result->where('c.status', '=', $request['status']);
        if ($request['college_uuid']) $result = $result->where('c.college_uuid', '=', $request['college_uuid']);
        $result = $result
            ->field('c.uuid,u.name,u.avatar,u.number,co.name as college_name,c.college_uuid,u.major,c.type,c.content,c.img,c.status')
            ->alias('c')
            ->join('user u','u.uuid = c.user_uuid')
            ->join('college co','co.uuid = c.college_uuid')
            ->where('c.is_deleted', 1)
            ->order('c.create_time desc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
        AdminLog::build()->add($userInfo['uuid'], '需求建议', '查询列表', $request);
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = Complaint::build()
            ->field('c.uuid,u.name,u.avatar,u.number,co.name as college_name,c.college_uuid,u.major,c.type,c.content,c.img,c.reply,c.status,c.create_time')
            ->alias('c')
            ->join('user u','u.uuid = c.user_uuid')
            ->join('college co','co.uuid = c.college_uuid')
            ->where('c.uuid', $id)
            ->where('c.is_deleted', 1)
            ->findOrFail();
        AdminLog::build()->add($userInfo['uuid'], '需求建议', '查询详情:' . $data->uuid, $id);
        return $data;
    }



    static public function cmsEdit($request, $userInfo)
    {
        try {
            $arr = [];
            if($request['reply']){
                $arr['status'] = 2;
                $arr['reply'] = $request['reply'];
            }
            if($request['status']){
                $arr['status'] = $request['status'];
            }
            $user = Complaint::build()->where('uuid', $request['uuid'])->findOrFail();
            $user->save($arr);
            AdminLog::build()->add($userInfo['uuid'], '需求建议', '更新', $request);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }


}
