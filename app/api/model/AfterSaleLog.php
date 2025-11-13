<?php

namespace app\api\model;

/**
 * 售后申请记录-模型
 */
class AfterSaleLog extends BaseModel
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
