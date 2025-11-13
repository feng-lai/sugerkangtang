<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\NotificationLogic;

/**
 * 动态-控制器
 * User: Yacon
 * Date: 2022-08-11
 * Time: 21:24
 */
class Notification extends Api
{
  public $restMethodList = 'get';


  public function _initialize()
  {
    parent::_initialize();
    //$this->userInfo = $this->miniValidateToken();
  }

  public function index()
  {
    $request = $this->selectParam([
      'page_index' => 1,      // 当前页码
      'page_size' => 10,      // 每页条目数
      'province',
      'city',
      'area'
    ]);
    $token = get_token();
    $this->userInfo = '';
    if($token){
      $this->userInfo = $this->miniValidateToken();
    }
    $result = NotificationLogic::cmsList($request,$this->userInfo);
    $this->render(200, ['result' => $result]);
  }

  public function read($id)
  {
    $result = NotificationLogic::cmsDetail($id);
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
