<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\CategoryLogic;

/**
 * 专家资料设置状态-控制器
 */
class CategorySetOrderNumber extends Api
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
        if(!$request['type']){
            $this->returnmsg(400, [], [], '', '', '类型不能为空');
        }
        $request['uuid'] = $id;
        $result = CategoryLogic::setOrderNumber($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }



}
