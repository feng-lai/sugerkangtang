<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\FeedbackLogic;

/**
 * 意见反馈-控制器
 * User: Yacon
 * Date: 2022-08-11
 * Time: 21:24
 */
class Feedback extends Api
{
  public $restMethodList = 'get|put|post|delete';


  public function _initialize()
  {
    parent::_initialize();
    $this->userInfo = $this->cmsValidateToken();
  }

  public function index()
  {
    $request = $this->selectParam([
      'page_index' => 1, // 当前页码
      'page_size' => 10, // 每页条目数
      'content'=>'',//搜索内容
      'delete'=>0 //1已删除 0未删除
    ]);
    $result = FeedbackLogic::cmsList($request,$this->userInfo);

    $this->render(200, ['result' => $result]);
  }

  public function read($id)
  {
    $result = FeedbackLogic::cmsDetail($id,$this->userInfo);
    $this->render(200, ['result' => $result]);
  }

   public function save()
   {
     $request = $this->selectParam([]);
     $this->check($request, "Feedback.save");
     $result = FeedbackLogic::cmsAdd($request);
     if (isset($result['msg'])) {
       $this->returnmsg(400, [], [], '', '', $result['msg']);
     } else {
       $this->render(200, ['result' => $result]);
     }
   }

  public function update($id)
  {
    $request = $this->selectParam([
      'content',
    ]);
    $request['uuid'] = $id;
    $result = FeedbackLogic::cmsEdit($request);
    if (isset($result['msg'])) {
      $this->returnmsg(400, [], [], '', '', $result['msg']);
    } else {
      $this->render(200, ['result' => $result]);
    }
  }

   public function delete($id)
   {
     $result = FeedbackLogic::cmsDelete($id,$this->userInfo);
     if (isset($result['msg'])) {
       $this->returnmsg(400, [], [], '', '', $result['msg']);
     } else {
       $this->render(200, ['result' => $result]);
     }
   }
}
