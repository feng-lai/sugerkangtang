<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\ParameterLogic;

/**
 * 商品参数-控制器
 */
class Parameter extends Api
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
            'name',
            'status',
            'type',
            'page_size'=>10,
            'page_index'=>1,
            'site_id',
        ]);
        $result = ParameterLogic::cmsList($request, $this->userInfo);
        $this->render(200, ['result' => $result]);
    }

    public function read($id)
    {
        $result = ParameterLogic::cmsDetail($id, $this->userInfo);
        $this->render(200, ['result' => $result]);
    }

    public function save()
    {
        $request = $this->selectParam([
            'name',
            'status',
            'type',
            'category_uuid',
            'site_id'=>1,
        ]);
        $this->check($request, "Parameter.save");
        $result = ParameterLogic::cmsAdd($request, $this->userInfo);
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
            'status',
            'type',
            'category_uuid',
        ]);
        $request['uuid'] = $id;
        $this->check($request, "Parameter.save");
        $result = ParameterLogic::cmsEdit($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function delete($id)
    {
        $result = ParameterLogic::cmsDelete($id, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

}
