<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\AdminLogExportLogic;

/**
 * 操作日志-控制器
 * User: Yacon
 * Date: 2022-08-11
 * Time: 21:24
 */
class AdminLogExport extends Api
{
  public $restMethodList = 'get|put|post|delete';


  public function _initialize()
  {
    parent::_initialize();
    $this->userInfo = $this->cmsValidateToken();
  }

  //导出
  public function index()
  {
      $request = $this->selectParam([
          'start_time',
          'end_time',
          'page_index' => 1,
          'page_size' => 10,
          'name',
          'type'
      ]);
    $result = AdminLogExportLogic::cmsList($request);
    $this->render(200, ['result' => $result]);
  }

  public function read($id)
  {
    $result = AdminLogic::cmsDetail($id);
    $this->render(200, ['result' => $result]);
  }

   public function save()
   {
     $request = $this->selectParam([
       'name',
       'mobile',
       'password',
       'email',
       'role_uuid'
     ]);
     $this->check($request, "Admin.save");
     $result = AdminLogic::cmsAdd($request);
     if (isset($result['msg'])) {
       $this->returnmsg(400, [], [], '', '', $result['msg']);
     } else {
       $this->render(200, ['result' => $result]);
     }
   }

  public function update($id)
  {
    $request = $this->selectParam([]);
    $request['uuid'] = $id;
    unset($request['id']);
    unset($request['version']);
    $result = AdminLogic::cmsEdit($request);
    if (isset($result['msg'])) {
      $this->returnmsg(400, [], [], '', '', $result['msg']);
    } else {
      $this->render(200, ['result' => $result]);
    }
  }

   public function delete($id)
   {
     $result = AdminLogLogic::cmsDelete($id);
     if (isset($result['msg'])) {
       $this->returnmsg(400, [], [], '', '', $result['msg']);
     } else {
       $this->render(200, ['result' => $result]);
     }
   }
}
