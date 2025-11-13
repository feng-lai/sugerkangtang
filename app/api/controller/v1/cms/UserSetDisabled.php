<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\UserLogic;

/**
 * 用户批量拉黑-控制器
 */
class UserSetDisabled extends Api
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
            'disabled',
            'day',
            'disabled_note',
            'type'
        ]);
        $request['uuid'] = $id;
        $this->check($request, "User.setDisabled");
        $result = UserLogic::setDisabled($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }



}
