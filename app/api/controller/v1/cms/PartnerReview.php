<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\PartnerReviewLogic;

/**
 * 合伙人审核-控制器
 */
class PartnerReview extends Api
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
            'review_status',
            'start_time',
            'end_time',
            'review_start_time',
            'review_end_time',
            'site_id'=>1,
            'page_size' => 10,
            'page_index' => 1,
        ]);
        $result = PartnerReviewLogic::cmsList($request, $this->userInfo);
        $this->render(200, ['result' => $result]);
    }

    public function read($id)
    {
        $result = PartnerReviewLogic::cmsDetail($id, $this->userInfo);
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
        $result = PartnerReviewLogic::cmsAdd($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function update($id)
    {
        $request = $this->selectParam([
            'review_status',
            'cash_out_persent',
            'cash_out_low',
            'note',
            'protocol'
        ]);
        $request['uuid'] = $id;
        $this->check($request, "PartnerReview.save");
        $result = PartnerReviewLogic::cmsEdit($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function delete($id)
    {
        $result = PartnerReviewLogic::cmsDelete($id, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

}
