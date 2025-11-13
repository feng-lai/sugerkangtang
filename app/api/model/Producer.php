<?php

namespace app\api\model;

/**
 * 出品方-模型
 */
class Producer extends BaseModel
{
    public static function build()
    {
        return new self();
    }

    public function dealers()
    {
        return $this->hasMany('Dealer', 'producer_uuid');
    }

    public function regions()
    {
        return $this->hasMany('Region', 'producer_uuid');
    }

    public function channels(){
        return $this->hasMany('Channel', 'producer_uuid');
    }

}
