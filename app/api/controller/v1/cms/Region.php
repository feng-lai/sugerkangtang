<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\RegionLogic;

/**
 * 大区推广员-控制器
 */
class Region extends Api
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
            'status',
            'site_id',
            'dealer_uuid',
            'page_size' => 10,
            'page_index' => 1,
        ]);
        $result = RegionLogic::cmsList($request, $this->userInfo);

        $this->render(200, ['result' => $result]);
    }

    public function read($id)
    {
        $result = RegionLogic::cmsDetail($id, $this->userInfo);
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
            'bank',
            'bank_number',
            'uname',
            'password'=>'123456',
            'note',
            'recommend_name',
            'dealer_uuid',
            'site_id' => 1,
        ]);
        $this->check($request, "Region.save");
        $result = RegionLogic::cmsAdd($request, $this->userInfo);
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
            'bank',
            'bank_number',
            'note',
            'uname',
            'recommend_name',
            'password',
            'dealer_uuid',
            'site_id' => 1,
        ]);
        $request['uuid'] = $id;
        $this->check($request, "Region.save");
        $result = RegionLogic::cmsEdit($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function delete($id)
    {
        $result = RegionLogic::cmsDelete($id, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

}
