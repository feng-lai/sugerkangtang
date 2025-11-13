<?php

namespace app\api\controller\v1\common;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\ContestantLogic;

/**
 * 选手-控制器
 * User: 
 * Date: 2023-03-20
 * Time: 11:56
 */
class Contestant extends Api
{
  public $restMethodList = 'get';


  public function _initialize()
  {
    parent::_initialize();
  }


  public function read($id)
  {
    $result = ContestantLogic::cmsDetail($id);
    $this->render(200, ['result' => $result]);
  }

}
