<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\RetailLogic;

/**
 * 分销员-控制器
 */
class Retail extends Api
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
            'keyword',
            'type',
            'status',
            'review_status',
            'start_time',
            'end_time',
            'review_start_time',
            'review_end_time',
            'site_id',
            'channel_uuid',
            'dealer_uuid',
            'producer_uuid',
            'region_uuid',
            'page_size' => 10,
            'page_index' => 1,
            'is_channel',
            'is_review_table'=>1
        ]);
        $result = RetailLogic::cmsList($request, $this->userInfo);
        $this->render(200, ['result' => $result]);
    }

    public function read($id)
    {
        $result = RetailLogic::cmsDetail($id, $this->userInfo);
        $this->render(200, ['result' => $result]);
    }

    public function save()
    {
        $request = $this->selectParam([
            'name',
            'contact_name',
            'phone',
            'address',
            'address_detail',
            'bank_name',
            'bank_number',
            'certificate',
            'business_license',
            'protocol',
            'site_id'=>1,
            'user_uuid',
            'puuid',
            'note',
            'cash_out_persent',
            'cash_out_low',
            'channel_uuid',
            'retail_sn'
        ]);
        $this->check($request, "Retail.save");
        $result = RetailLogic::cmsAdd($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function update($id)
    {
        $request = $this->selectParam([
            'name',
            'contact_name',
            'phone',
            'address',
            'address_detail',
            'bank_name',
            'bank_number',
            'certificate',
            'business_license',
            'protocol',
            'user_uuid',
            'puuid',
            'note',
            'cash_out_persent',
            'cash_out_low',
            'channel_uuid',
            'retail_sn'
        ]);
        $request['uuid'] = $id;
        $this->check($request, "Retail.save");
        $result = RetailLogic::cmsEdit($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function delete($id)
    {
        $result = RetailLogic::cmsDelete($id, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

}
