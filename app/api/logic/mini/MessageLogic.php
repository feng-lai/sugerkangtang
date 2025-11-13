<?php

namespace app\api\logic\mini;

use app\api\model\Message;
use think\Exception;
use think\Db;

/**
 * 我的消息-逻辑
 */
class MessageLogic
{
    static public function List($request,$userInfo)
    {
        try {
            $result = Message::build()->where('is_deleted',1)->where('user_uuid',$userInfo['uuid'])->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
            return $result;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
    static public function Edit($uuid,$userInfo)
    {
        try {
            $msg = Message::build()->where('user_uuid',$userInfo['uuid'])->where('uuid',$uuid)->findOrFail();
            $msg->save(['read'=>1]);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

}
