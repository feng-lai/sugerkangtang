<?php

namespace app\api\model;

/**
 * @property integer area_id
 * @property integer parent_id
 * @property string area_name
 * @property integer level
 * @property string created_at
 * @property string updated_at
 * @property string city_code
 * @property string center
 * @property string area_code
 */
class Region extends BaseModel
{
  public static function build()
  {
    return new self();
  }
}
