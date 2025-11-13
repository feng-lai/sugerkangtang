<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\DeskLogic;

/**
 * 工作台-控制器
 */
class Desk extends Api
{
    public $restMethodList = 'get';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function read($id)
    {
        $request = $this->selectParam([
            'site_id'=>1
        ]);
        switch ($id) {
            case 'td_yd':
                //今日较昨日的数据
                $result = DeskLogic::td_yd($request,$this->userInfo);
                break;
            case 'pending':
                $result = DeskLogic::pending($request,$this->userInfo);
                break;

            case 'analyze':
                $result = DeskLogic::analyze($request,$this->userInfo);
            default:
        }
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
