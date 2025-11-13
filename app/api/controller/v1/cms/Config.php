<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\ConfigLogic;

/**
 * 配置-控制器
 */
class Config extends Api
{
    public $restMethodList = 'get|put|post';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function index()
    {
        $request = $this->selectParam([
            'key',
        ]);
        $result = ConfigLogic::cmsList($request['key'], $this->userInfo);
        $this->render(200, ['result' => $result]);
    }

    public function read($id)
    {
        $result = ConfigLogic::cmsDetail($id, $this->userInfo);
        $this->render(200, ['result' => $result]);
    }

    public function save()
    {
        $request = $this->selectParam([
            'data',
        ]);
        $this->check($request, 'Config.save');
        $result = ConfigLogic::cmsSave($request['data'], $this->userInfo);
        $this->render(200, ['result' => $result]);
    }


    public function update($id)
    {
        $request = $this->selectParam([
            'value',
            'site_id' => 1
        ]);
        $request['key'] = $id;
        $this->check($request, 'Config.edit');
        $result = ConfigLogic::cmsEdit($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

}
