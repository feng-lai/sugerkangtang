<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use app\api\logic\cms\PartnerOrderOutlineLogic;

/**
 * 2+1线下分润订单-控制器
 */
class PartnerOrderOutline extends Api
{
    public $restMethodList = 'get|post|put|delete';

    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function index()
    {
        $request = $this->selectParam([
            'site_id' => 1,
            'producer_uuid',
            'name',
            'start_time',
            'end_time',
            'status',
            'order_id',
            'page_size' => 10,
            'page_index' => 1
        ]);
        $result = PartnerOrderOutlineLogic::List($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

}
