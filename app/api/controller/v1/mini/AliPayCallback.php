<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use think\Request;
use app\api\logic\mini\AliPayCallbackLogic;

/**
 * 支付宝支付回调-控制器
 * User: Yacon
 * Date: 2022-07-21
 * Time: 14:31
 */
class AliPayCallback extends Api
{
  public $restMethodList = 'post';

  public function save()
  {
    $request = Request::instance()->param();
    $result = AliPayCallbackLogic::miniAdd($request);
    if (isset($result['msg'])) {
      $this->returnmsg(400, [], [], '', '', $result['msg']);
    } else {
      $this->render(200, ['result' => $result]);
    }
  }

  // public function update($id){
  //   $request = $this->selectParam([]);
  //   $request['uuid'] = $id;
  //   $result = UserLogic::miniEdit($request,$this->userInfo);
  //   if (isset($result['msg'])) {
  //     $this->returnmsg(400, [], [], '', '', $result['msg']);
  //   } else {
  //     $this->render(200, ['result' => $result]);
  //   }
  // }

  // public function delete($id){
  //   $result = UserLogic::miniDelete($id,$this->userInfo);
  //   if (isset($result['msg'])) {
  //     $this->returnmsg(400, [], [], '', '', $result['msg']);
  //   } else {
  //     $this->render(200, ['result' => $result]);
  //   }
  // }
}
