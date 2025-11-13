<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\AdminRoleLogic;

/**
 * 后台角色状态修改-控制器
 * User: Yacon
 * Date: 2022-08-11
 * Time: 21:24
 */
class AdminRoleStatus extends Api
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
            'status'
        ]);
        $request['uuid'] = $id;
        $result = AdminRoleLogic::setStatus($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

}
