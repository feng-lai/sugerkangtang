<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\CollegeLogic;

/**
 * 书院-控制器
 */
class College extends Api
{
  public $restMethodList = 'get|post|put|delete';


  public function _initialize()
  {
    parent::_initialize();
    $this->userInfo = $this->cmsValidateToken();
  }

  public function index()
  {
    $request = $this->selectParam([
      'name',
      'page_size'=>10,
      'page_index'=>1,
    ]);
    $result = CollegeLogic::cmsList($request,$this->userInfo);

    $this->render(200, ['result' => $result]);
  }

  public function read($id)
  {
    $result = CollegeLogic::cmsDetail($id,$this->userInfo);
    $this->render(200, ['result' => $result]);
  }

   public function save()
   {
     $request = $this->selectParam([
         'name',
         'img',
         'dsc'
     ]);
     $this->check($request, "College.save");
     $result = CollegeLogic::cmsAdd($request,$this->userInfo);
     if (isset($result['msg'])) {
       $this->returnmsg(400, [], [], '', '', $result['msg']);
     } else {
       $this->render(200, ['result' => $result]);
     }
   }

  public function update($id)
  {
    $request = $this->selectParam([
        'name',
        'img',
        'dsc'
    ]);
    $result = CollegeLogic::cmsEdit($request,$this->userInfo,$id);
    if (isset($result['msg'])) {
      $this->returnmsg(400, [], [], '', '', $result['msg']);
    } else {
      $this->render(200, ['result' => $result]);
    }
  }

   public function delete($id)
   {
     $result = CollegeLogic::cmsDelete($id,$this->userInfo);
     if (isset($result['msg'])) {
       $this->returnmsg(400, [], [], '', '', $result['msg']);
     } else {
       $this->render(200, ['result' => $result]);
     }
   }

}
