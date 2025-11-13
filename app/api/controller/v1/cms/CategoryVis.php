<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\CategoryLogic;

/**
 * 分类显示/隐藏-控制器
 */
class CategoryVis extends Api
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
      'vis',
    ]);
    $this->check($request, "Category.vis");
    $result = CategoryLogic::setVis($request,$this->userInfo,$id);
    if (isset($result['msg'])) {
      $this->returnmsg(400, [], [], '', '', $result['msg']);
    } else {
      $this->render(200, ['result' => $result]);
    }
  }


}
