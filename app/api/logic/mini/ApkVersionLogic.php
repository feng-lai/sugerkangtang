<?php

namespace app\api\logic\mini;

use app\api\model\ApkVersion;
use think\Exception;
use think\Db;

/**
 * 礼物-逻辑
 * User: Yacon
 * Date: 2022-07-21
 * Time: 14:31
 */
class ApkVersionLogic
{

  static public function cmsList($request)
  {
    $map['is_deleted'] = 1;
    $request['type']? $map['type'] = $request['type']:'';
    $result = ApkVersion::build()->order('create_time desc')->where($map)->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
    return $result;
  }
}
