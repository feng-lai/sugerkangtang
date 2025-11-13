<?php

namespace app\api\model;

/**
 * 售后申请-模型
 */
class AfterSale extends BaseModel
{
    public static function build()
    {
        return new self();
    }
    public function getProductAttributeUuidAttr($value)
    {
        return json_decode($value);
    }

    public function setProductAttributeUuidAttr($value)
    {
        return json_encode($value);
    }

    public function getImgAttr($value)
    {
        return json_decode($value);
    }

    public function setImgAttr($value)
    {
        return json_encode($value);
    }

}
