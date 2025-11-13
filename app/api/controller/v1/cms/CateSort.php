<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\CateLogic;

/**
 * 分类上移/下移-控制器
 */
class CateSort extends Api
{
  public $restMethodList = 'put';


  public function _initialize()
  {
    parent::_initialize();
    $this->userInfo = $this->cmsValidateToken();
  }

  public function update($id)
  {
    $request = $this->selectParam([
      'type',
    ]);
    $this->check($request, "Cate.sort");
    $result = CateLogic::setSort($request,$this->userInfo,$id);
    if (isset($result['msg'])) {
      $this->returnmsg(400, [], [], '', '', $result['msg']);
    } else {
      $this->render(200, ['result' => $result]);
    }
  }


}
