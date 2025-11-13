<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\ProblemLogic;

/**
 * 常见问题-控制器
 * User: Yacon
 * Date: 2022-08-11
 * Time: 21:24
 */
class Problem extends Api
{
  public $restMethodList = 'get';


  public function _initialize()
  {
    parent::_initialize();
    $this->userInfo = $this->miniValidateToken();
  }

  public function index()
  {
    $result = ProblemLogic::cmsList();
    $this->render(200, ['result' => $result]);
  }

  public function read($id)
  {
    $result = ConfigLogic::cmsDetail($id);
    $this->render(200, ['result' => $result]);
  }

   public function save()
   {
     $request = $this->selectParam([]);
     $this->check($request, "Config.save");
     $result = ConfigLogic::cmsAdd($request);
     if (isset($result['msg'])) {
       $this->returnmsg(400, [], [], '', '', $result['msg']);
     } else {
       $this->render(200, ['result' => $result]);
     }
   }

  public function update($id)
  {
    $request = $this->selectParam([
      'value',
    ]);
    $request['key'] = $id;
    $result = ConfigLogic::cmsEdit($request);
    if (isset($result['msg'])) {
      $this->returnmsg(400, [], [], '', '', $result['msg']);
    } else {
      $this->render(200, ['result' => $result]);
    }
  }

   public function delete($id)
   {
     $result = ConfigLogic::cmsDelete($id);
     if (isset($result['msg'])) {
       $this->returnmsg(400, [], [], '', '', $result['msg']);
     } else {
       $this->render(200, ['result' => $result]);
     }
   }
}
