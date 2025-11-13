<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use app\api\logic\cms\CashOutLogic;

/**
 * 提现-控制器
 */
class CashOut extends Api
{
    public $restMethodList = 'get|post|put|delete';

    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function save()
    {
        $request = $this->selectParam([
            'name',
            'phone',
            'address',
            'province',
            'city',
            'district',
            'tag',
            'user_uuid',
            'is_default',
            'site_id' => 1
        ]);
        $this->check($request, 'Address.cms_save');
        $result = CashOutLogic::Add($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function index()
    {
        $request = $this->selectParam([
            'site_id' => 1,
            'keyword',
            'start_time',
            'end_time',
            'status',
            'retail_uuid',
            'user_uuid',
            'page_size' => 10,
            'page_index' => 1
        ]);
        $result = CashOutLogic::List($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function read($id)
    {
        $result = CashOutLogic::Detail($id, $this->userInfo);
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
            'phone',
            'address',
            'province',
            'city',
            'district',
            'tag',
            'is_default'
        ]);
        $request['uuid'] = $id;
        $this->check($request, 'Address.save');
        $result = CashOutLogic::Edit($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function delete($id)
    {
        $result = CashOutLogic::Delete($id, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
