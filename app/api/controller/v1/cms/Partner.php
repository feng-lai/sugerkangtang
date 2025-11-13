<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\PartnerLogic;

/**
 * 合伙人-控制器
 */
class Partner extends Api
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
            'start_time',
            'end_time',
            'site_id'=>1,
            'producer_uuid',
            'is_producer',
            'page_size' => 10,
            'page_index' => 1,
        ]);
        $result = PartnerLogic::cmsList($request, $this->userInfo);
        $this->render(200, ['result' => $result]);
    }

    public function read($id)
    {
        $result = PartnerLogic::cmsDetail($id, $this->userInfo);
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
            'protocol',
            'user_uuid',
            'note',
            'cash_out_persent',
            'cash_out_low',
            'producer_uuid',
            'partner_sn',
            'site_id'=>1,
        ]);
        $this->check($request, "Partner.save");
        $result = PartnerLogic::cmsAdd($request, $this->userInfo);
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
            'protocol',
            'user_uuid',
            'note',
            'cash_out_persent',
            'cash_out_low',
            'producer_uuid',
            'partner_sn',
        ]);
        $request['uuid'] = $id;
        $this->check($request, "Partner.save");
        $result = PartnerLogic::cmsEdit($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function delete($id)
    {
        $result = PartnerLogic::cmsDelete($id, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

}
