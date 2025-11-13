<?php

namespace app\api\model;

use Exception;

/**
 * 消息发送历史-模型
 */
class MessageHistory extends BaseModel
{
    public static function build()
    {
        return new self();
    }
}
