<?php

namespace app\api\model;

/**
 * 订单日志-模型
 */
class OrderLog extends BaseModel
{
    public static function build()
    {
        return new self();
    }

    public function getContentAttr($value)
    {
        return json_decode($value);
    }

    public function setContentAttr($value)
    {
        return json_encode($value);
    }

}
