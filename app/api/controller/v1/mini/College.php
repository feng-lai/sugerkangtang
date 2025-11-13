<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\CollegeLogic;

/**
 * 书院-控制器
 */
class College extends Api
{
  public $restMethodList = 'get';

  public function index()
  {
    $result = CollegeLogic::List();
    if (isset($result['msg'])) {
      $this->returnmsg(400, [], [], '', '', $result['msg']);
    } else {
      $this->render(200, ['result' => $result]);
    }
  }
}
