<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\BillLogic;

/**
 * 平台流水-控制器
 * User: Yacon
 * Date: 2022-08-11
 * Time: 21:24
 */
class Bill extends Api
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
            'status',
            'type',
            'page_size' => 10,
            'page_index' => 1,
            'start_time',
            'end_time',
            'site_id'=>1,
            'retail_uuid',
            'bill_id',
            'user_uuid'
        ]);
        $result = BillLogic::cmsList($request, $this->userInfo);

        $this->render(200, ['result' => $result]);
    }

    public function read($id)
    {
        $request = $this->selectParam([
            'start_time',
            'end_time',
            'site_id'=>1,
            'type'
        ]);
        switch ($id){
            case 'stat':
                $result = BillLogic::stat($request,$this->userInfo);
                break;
            case 'profit':
                $result = BillLogic::profit($request,$this->userInfo);
                break;
        }
        $this->render(200, ['result' => $result]);
    }

    public function save()
    {
        $request = $this->selectParam([]);
        $this->check($request, "RechangeSet.save");
        $result = RechangeSetLogic::cmsAdd($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function update($id)
    {
        $request = $this->selectParam([
            'coins',
            'price'
        ]);
        $request['uuid'] = $id;
        $result = RechangeSetLogic::cmsEdit($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function delete($id)
    {
        $result = RechangeSetLogic::cmsDelete($id, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
