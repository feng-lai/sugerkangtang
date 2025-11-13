<?php

namespace app\api\controller\v1\common;

use app\api\controller\Api;
use app\api\logic\common\GeocoderLogic;

/**
 * 用户端-用户登陆-获取位置信息
 * 
 * @author Yacon
 */
class Geocoder extends Api
{
    public $restMethodList = 'post|get';

    public function save()
    {
        $request = $this->selectParam([
            'longitude', // 经度
            'latitude', // 纬度
        ]);

        $result = GeocoderLogic::appAdd($request);

        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
    public function index()
    {
      $request = $this->selectParam([
        'address',
      ]);

      $result = GeocoderLogic::address($request);

      if (isset($result['msg'])) {
        $this->returnmsg(400, [], [], '', '', $result['msg']);
      } else {
        $this->render(200, ['result' => $result]);
      }
    }
}
