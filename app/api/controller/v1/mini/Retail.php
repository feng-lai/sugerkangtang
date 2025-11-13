<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\RetailLogic;

/**
 * 推广员-控制器
 */
class Retail extends Api
{
    public $restMethodList = 'get|post|put|delete';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->miniValidateToken();
    }

    public function index()
    {
        $result = RetailLogic::miniList($this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function save()
    {
        $request = $this->selectParam([
            "name",
            "phone",
            "address",
            "address_detail",
            "contact_name",
            "bank_name",
            "bank_number",
            "business_license",
            "protocol",
            "site_id"=>1,
            "sence"=>'mini'
        ]);
        $uuid = \app\api\model\Retail::build()->where('user_uuid',$this->userInfo['uuid'])->value('uuid');
        $request['uuid'] = $uuid;
        $this->check($request,'Retail.miniSave');
        $result = RetailLogic::miniSave($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
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
        $result = RetailLogic::Team($request,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }


}
