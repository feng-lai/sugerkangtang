<?php

namespace app\api\model;

/**
 * 规格库-模型
 * User:
 * Date:
 * Time:
 */
class Attribute extends BaseModel
{
    public static function build()
    {
        return new self();
    }

    public function getValueAttr($value)
    {
        return json_decode($value);
    }

    public function setValueAttr($value)
    {
        return json_encode($value);
    }

}
