<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\UserCourseLogic;

/**
 * 用户拼课-控制器
 */
class UserCourse extends Api
{
    public $restMethodList = 'get|post|put|delete';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->miniValidateToken();
    }

    public function index()
    {
        $request = $this->selectParam([
            'page_size' => 10,
            'page_index' => 1,
            'status'
        ]);
        $result = UserCourseLogic::miniList($request,$this->userInfo);
        $this->render(200, ['result' => $result]);
    }
    public function read($id)
    {
        $result = UserCourseLogic::miniDetail($id,$this->userInfo);
        $this->render(200, ['result' => $result]);
    }

}
