<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use app\api\logic\cms\PartnerOrderStatLogic;

/**
 * 2+1推广统计-控制器
 */
class PartnerOrderStat extends Api
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
            'site_id' => 1,
            'page_size' => 10,
            'page_index' => 1,
            'start_time',
            'end_time',
            'type',
            'outline_type'=>1,
            'user_uuid',
            'order_type',
            'producer_uuid',
            'dealer_uuid',
            'region_uuid',
            'channel_uuid',
        ]);

        switch ($id){
            case 'stat':
                $result = PartnerOrderStatLogic::stat($request, $this->userInfo);
            break;
            case 'ranking':
                $result = PartnerOrderStatLogic::ranking($request, $this->userInfo);
                break;
            case 'ranking_order':
                $result = PartnerOrderStatLogic::ranking_order($request, $this->userInfo);
                break;
            case 'outline':
                $result = PartnerOrderStatLogic::outline($request, $this->userInfo);
                break;
            case 'outline_order':
                $result = PartnerOrderStatLogic::outline_order($request, $this->userInfo);
                break;
        }
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

}
