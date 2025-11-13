<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\ApkVersionLogic;
use app\attributes\Menu;

/**
 * app版本-控制器
 * User: Yacon
 * Date: 2022-08-11
 * Time: 21:24
 */
class ApkVersion extends Api
{
  public $restMethodList = 'get|post|put|delete';


  public function _initialize()
  {
    parent::_initialize();
    $this->userInfo = $this->cmsValidateToken();
  }
  #[Menu('查看app版本-列表')]
  public function index()
  {
    $request = $this->selectParam([
      'keyword_search',
      'type',
      'page_size'=>10,
      'page_index'=>1
    ]);
    $result = ApkVersionLogic::cmsList($request,$this->userInfo);
    $this->render(200, ['result' => $result]);
  }

  public function read($id)
  {
    $result = ApkVersionLogic::cmsDetail($id,$this->userInfo);
    $this->render(200, ['result' => $result]);
  }

   public function save()
   {
     $request = $this->selectParam([
       'note',
       'v',
       'type'=>1,
       'url',
       'apk',
       'upgrade',
       'file_type',
     ]);
     $this->check($request, "ApkVersion.save");
     $result = ApkVersionLogic::cmsAdd($request,$this->userInfo);
     if (isset($result['msg'])) {
       $this->returnmsg(400, [], [], '', '', $result['msg']);
     } else {
       $this->render(200, ['result' => $result]);
     }
   }

  public function update($id)
  {
    $request = $this->selectParam([
      'note',
      'v',
      'type',
      'url',
      'apk',
      'upgrade',
      'file_type',
    ]);
    $request['uuid'] = $id;
    $result = ApkVersionLogic::cmsEdit($request,$this->userInfo);
    if (isset($result['msg'])) {
      $this->returnmsg(400, [], [], '', '', $result['msg']);
    } else {
      $this->render(200, ['result' => $result]);
    }
  }

   public function delete($id)
   {
     $result = ApkVersionLogic::cmsDelete($id,$this->userInfo);
     if (isset($result['msg'])) {
       $this->returnmsg(400, [], [], '', '', $result['msg']);
     } else {
       $this->render(200, ['result' => $result]);
     }
   }
}
