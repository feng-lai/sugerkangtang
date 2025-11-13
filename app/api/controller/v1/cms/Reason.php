<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\ReasonLogic;

/**
 * 原因-控制器
 */
class Reason extends Api
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
            'page_size'=>10,
            'page_index'=>1,
            'type',
            'site_id'=>1
        ]);
        $result = ReasonLogic::cmsList($request,$this->userInfo);

        $this->render(200, ['result' => $result]);
    }

    public function read($id)
    {
        $result = ReasonLogic::cmsDetail($id, $this->userInfo);
        $this->render(200, ['result' => $result]);
    }

    public function save()
    {
        $request = $this->selectParam([
            'content',
            'status',
            'type',
            'site_id'=>1
        ]);
        $this->check($request, "Reason.save");
        $result = ReasonLogic::cmsAdd($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function update($id)
    {
        $request = $this->selectParam([
            'content',
            'status',
        ]);
        $request['uuid'] = $id;
        $this->check($request, "Reason.edit");
        $result = ReasonLogic::cmsEdit($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function delete($id)
    {
        $result = ReasonLogic::cmsDelete($id, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
