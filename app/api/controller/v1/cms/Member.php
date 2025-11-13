<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\MemberLogic;

/**
 * 会员设置-控制器
 */
class Member extends Api
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
      'page_size'=>10,
      'page_index'=>1
    ]);
    $result = MemberLogic::cmsList($request,$this->userInfo);

    $this->render(200, ['result' => $result]);
  }

  public function read($id)
  {
    $result = MemberLogic::cmsDetail($id,$this->userInfo);
    $this->render(200, ['result' => $result]);
  }

   public function save()
   {
     $request = $this->selectParam([
         'name',
         'price',
         'img',
         'bg',
         'text_color',
         'pid',
         'discount',
         'all_discount',
         'is_fee',
         'doubled'
     ]);
     $this->check($request, "Member.save");
     $result = MemberLogic::cmsAdd($request,$this->userInfo);
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
        'price',
        'img',
        'bg',
        'text_color',
        'pid',
        'discount',
        'all_discount',
        'is_fee',
        'doubled'
    ]);
    $result = MemberLogic::cmsEdit($request,$this->userInfo,$id);
    if (isset($result['msg'])) {
      $this->returnmsg(400, [], [], '', '', $result['msg']);
    } else {
      $this->render(200, ['result' => $result]);
    }
  }

   public function delete($id)
   {
     $result = MemberLogic::cmsDelete($id,$this->userInfo);
     if (isset($result['msg'])) {
       $this->returnmsg(400, [], [], '', '', $result['msg']);
     } else {
       $this->render(200, ['result' => $result]);
     }
   }

}
