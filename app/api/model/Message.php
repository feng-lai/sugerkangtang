<?php

namespace app\api\model;

use Exception;

/**
 * 消息-模型
 */
class Message extends BaseModel
{
    public static function build()
    {
        return new self();
    }
}
