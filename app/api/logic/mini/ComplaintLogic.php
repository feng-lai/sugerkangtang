<?php

namespace app\api\logic\mini;

use app\api\model\Complaint;
use think\Exception;
use think\Db;

/**
 * 投诉建议-逻辑
 */
class ComplaintLogic
{
    static public function Add($request, $userInfo)
    {
        try {
            $data = [
                'uuid' => uuid(),
                'user_uuid' => $userInfo['uuid'],
                'college_uuid' => $userInfo['college_uuid'],
                'content' => $request['content'],
                'type' => $request['type'],
                'anonymous' => $request['anonymous'],
                'img' => $request['img'],
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
            ];
            Complaint::build()->insert($data);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function List($request, $userInfo)
    {
        try {
            $result = Complaint::build()
                ->field('uuid,content,img,type,status,reply,anonymous,reply_time,create_time')
                ->where('user_uuid', $userInfo['uuid'])
                ->where('is_deleted',1)
                ->order('create_time desc')
                ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
            return $result;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
