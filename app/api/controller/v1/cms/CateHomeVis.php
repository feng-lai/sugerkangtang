<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\CateLogic;

/**
 * 三级分类设置首页显示/不显示-控制器
 */
class CateHomeVis extends Api
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
            'home_vis',
        ]);
        $request['uuid'] = $id;
        $result = CateLogic::homeVis($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }


}
