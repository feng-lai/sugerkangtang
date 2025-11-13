<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\EvaluateLogic;

/**
 * 评价-控制器
 */
class Evaluate extends Api
{
    public $restMethodList = 'get';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function index()
    {
        $request = $this->selectParam([
            'admin_uuid',
            'page_size' => 10,
            'page_index' => 1,
        ]);
        $result = EvaluateLogic::cmsList($request, $this->userInfo);

        $this->render(200, ['result' => $result]);
    }
    public function read($id)
    {
        $result = EvaluateLogic::cmsDetail($id,$this->userInfo);
        $this->render(200, ['result' => $result]);
    }

}
