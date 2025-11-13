<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\ComplaintLogic;

/**
 * 需求建议-控制器
 */
class Complaint extends Api
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
      'college_uuid',
      'status',
      'type',
      'page_size'=>10,
      'page_index'=>1,
    ]);
    $result = ComplaintLogic::cmsList($request,$this->userInfo);

    $this->render(200, ['result' => $result]);
  }

  public function read($id)
  {
    $result = ComplaintLogic::cmsDetail($id,$this->userInfo);
    $this->render(200, ['result' => $result]);
  }


  public function update($id)
  {
    $request = $this->selectParam([
        'reply',
        'status',
    ]);
    $request['uuid'] = $id;
    $result = ComplaintLogic::cmsEdit($request,$this->userInfo);
    if (isset($result['msg'])) {
      $this->returnmsg(400, [], [], '', '', $result['msg']);
    } else {
      $this->render(200, ['result' => $result]);
    }
  }


}
