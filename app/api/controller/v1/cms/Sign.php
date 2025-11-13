<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\SignLogic;

/**
 * 签到-控制器
 */
class Sign extends Api
{
    public $restMethodList = 'get|delete|post';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function save(){
        $request = $this->selectParam([
            'user_uuid',
            'course_uuid'
        ]);
        $this->check($request, "Sign.cms_save");
        $result = SignLogic::cmsAdd($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function index()
    {
        $request = $this->selectParam([
            'user_uuid',
            'page_size' => 10,
            'page_index' => 1
        ]);
        $result = SignLogic::cmsList($request, $this->userInfo);
        $this->render(200, ['result' => $result]);
    }
    public function delete($id)
    {
        $result = SignLogic::cmsDelete($id, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
