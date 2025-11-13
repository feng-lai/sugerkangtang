<?php

namespace app\api\model;

/**
 * 管理员角色-模型
 * User: Yacon
 * Date: 2022-08-11
 * Time: 20:43
 */
class AdminRole extends BaseModel
{
    public static function build()
    {
        return new self();
    }

    /**
     * 生成ID号
     */
    public function createID()
    {
        $number = $this->max('serial_number');
        $number++;
        $count = strlen($number);
        $pre = 'AM';
        for ($i = 0; $i < 7 - $count; $i++) {
            $pre .= '0';
        }
        $result = $pre .  $number;
        return [$number, $result];
    }

    public function getMenusAttr($value)
    {
      return json_decode($value);
    }

    public function setMenusAttr($value)
    {
      return json_encode($value);
    }
}
