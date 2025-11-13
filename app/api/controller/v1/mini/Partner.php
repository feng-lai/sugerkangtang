<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\PartnerLogic;

/**
 * 合伙人-控制器
 */
class Partner extends Api
{
    public $restMethodList = 'get';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->miniValidateToken();
    }


    public function read($id)
    {
        $request = $this->selectParam([
            'site_id'=>1,
            'page_index'=>1,
            'page_size'=>10,
            'type',
            'name'
        ]);
        $result = PartnerLogic::Team($request,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }


}
