<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\FeedbackLogic;

/**
 * 意见反馈-控制器
 * User: n
 * Date: 2022-07-21
 * Time: 14:31
 */
class Feedback extends Api
{
  public $restMethodList = 'post';

  public function _initialize()
  {
    parent::_initialize();
    $this->userInfo = $this->miniValidateToken();
  }

  public function index()
  {
    $result = SignLogic::miniList($this->userInfo);
    $this->render(200, ['result' => $result]);
  }

  public function save()
  {
    $request = $this->selectParam([
      'content'
    ]);
    $request['user_uuid'] = $this->userInfo['uuid'];
    $this->check($request, "Feedback.save");
    $result = FeedbackLogic::miniAdd($request);
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
