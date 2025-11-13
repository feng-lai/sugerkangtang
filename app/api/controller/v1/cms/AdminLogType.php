<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\model\AdminLog;

/**
 * 操作日志-控制器
 * User: Yacon
 * Date: 2022-08-11
 * Time: 21:24
 */
class AdminLogType extends Api
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
      'keyword_search',
    ]);
    $map = [];
    if($request['keyword_search']){
      $map['action'] = ['like','%'.$request['keyword_search'].'%'];
    }
    $result = AdminLog::build()->where($map)->group('action')->column('action');
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
     $result = AdminLogic::cmsDelete($id);
     if (isset($result['msg'])) {
       $this->returnmsg(400, [], [], '', '', $result['msg']);
     } else {
       $this->render(200, ['result' => $result]);
     }
   }
}
