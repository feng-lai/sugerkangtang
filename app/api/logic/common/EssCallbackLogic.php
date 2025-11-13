<?php

namespace app\api\logic\common;

use app\api\model\Contestant;
use think\Exception;
use think\Db;

/**
 * 腾讯电子签-回调-逻辑
 * User: Yacon
 * Date: 2023-03-31
 * Time: 16:41
 */
class EssCallbackLogic
{

  static private $callbackUrlKey = '4370933E49A246BE90CD920D190145FC';

  static public function commonAdd($request, $headers)
  {
    try {
      Db::startTrans();
      $result = self::aesDe($request);
      $result = objToArray(json_decode($result));
      
      $flowId = $result['FlowId'];

      // 签署回调
      if ($result['CallbackType'] == 'sign') {
        //已签署
        if ($result['FlowCallbackStatus'] == 4) {
          $contestant = Contestant::build()->where(['sign_flow_id' => $flowId])->find();
          $contestant->state = 4;
          $contestant->save();
        }
      }

      Db::commit();
      return true;
    } catch (Exception $e) {
      Db::rollback();
      throw new Exception($e->getMessage(), 500);
    }
  }

  /**
   * 解密
   * @param $data 密文
   */
  static private function aesDe($data)
  {
    return openssl_decrypt(base64_decode($data),  'AES-256-CBC', self::$callbackUrlKey, OPENSSL_RAW_DATA, substr(self::$callbackUrlKey, 0, 16));
  }
}
