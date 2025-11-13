<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use app\api\logic\cms\CommissionOrderStatLogic;

/**
 * 推广统计-控制器
 */
class CommissionOrderStat extends Api
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
            'retail_uuid',
            'order_type',
            'producer_uuid',
            'dealer_uuid',
            'region_uuid',
            'channel_uuid',
        ]);

        switch ($id){
            case 'stat':
                $result = CommissionOrderStatLogic::stat($request, $this->userInfo);
            break;
            case 'ranking':
                $result = CommissionOrderStatLogic::ranking($request, $this->userInfo);
                break;
            case 'ranking_order':
                $result = CommissionOrderStatLogic::ranking_order($request, $this->userInfo);
                break;
            case 'outline':
                $result = CommissionOrderStatLogic::outline($request, $this->userInfo);
                break;
            case 'outline_order':
                $result = CommissionOrderStatLogic::outline_order($request, $this->userInfo);
                break;
        }
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

}
