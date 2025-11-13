<?php

namespace app\api\controller\v1\common;

use app\api\controller\Api;
use app\api\logic\common\ExpressInfoLogic;

/**
 * 物流信息-控制器
 * User: Yacon
 * Date: 2022-03-13
 * Time: 23:50
 */
class ExpressInfo extends Api
{
  public $restMethodList = 'get|post|put|delete';

  public function save()
  {
    $request = $this->selectParam([
      'num', // 物流单号
      'com', // 快递公司的编码
      'phone' // 收、寄件人的电话号码，顺丰速运和丰网速运必填，其他快递公司选填
    ]);
    $result = ExpressInfoLogic::commonAdd($request, $this->userInfo);
    if (isset($result['msg'])) {
      $this->returnmsg(400, [], [], '', '', $result['msg']);
    } else {
      $this->render(200, ['result' => $result]);
    }
  }
}
