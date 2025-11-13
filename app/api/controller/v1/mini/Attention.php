<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\AttentionLogic;

/**
 * 关注-控制器
 * User: Yacon
 * Date: 2022-07-21
 * Time: 14:31
 */
class Attention extends Api
{
  public $restMethodList = 'get|post|put|delete';


  public function _initialize()
  {
    parent::_initialize();
    $this->userInfo = $this->miniValidateToken();
  }

  public function index()
  {
    $request = $this->selectParam([
      'page_index' => 1, // 当前页码
      'page_size' => 10, // 每页条目数
    ]);
    $result = AttentionLogic::cmsList($request,$this->userInfo);
    $this->render(200, ['result' => $result]);
  }


  public function save()
  {
    $request = $this->selectParam([
      'contestant_uuid', // 选手uuid
      'type', // 1关注 -1取消
    ]);
    $this->check($request, "Attention.save");
    $result = AttentionLogic::miniAdd($request, $this->userInfo);
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
