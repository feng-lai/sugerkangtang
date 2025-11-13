<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\FeelLogic;

/**
 * 心得-控制器
 */
class Feel extends Api
{
    public $restMethodList = 'get|put';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function index()
    {
        $request = $this->selectParam([
            'user_uuid',
            'page_size' => 10,
            'page_index' => 1,
            'admin_uuid',
            'course_name'
        ]);
        $result = FeelLogic::cmsList($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
    public function read($id)
    {
        $result = FeelLogic::cmsDetail($id, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
    public function update($id){
        $request = $this->selectParam([
            'status',
            'reason'
        ]);
        $request['uuid'] = $id;
        $result = FeelLogic::cmsEdit($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
