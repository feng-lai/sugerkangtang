<?php

namespace app\api\model;

/**
 * 体检报告-模型
 */
class MedicalReport extends BaseModel
{
    public static function build()
    {
        return new self();
    }

    public function getExtAttr($value)
    {
        return json_decode($value);
    }

    public function setExtAttr($value)
    {
        return json_encode($value);
    }

    public function getFileAttr($value)
    {
        return json_decode($value);
    }

    public function setFileAttr($value)
    {
        return json_encode($value);
    }


    public function getCaFileAttr($value)
    {
        return json_decode($value);
    }

    public function setCaFileAttr($value)
    {
        return json_encode($value);
    }
}
