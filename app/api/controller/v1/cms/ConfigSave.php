<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\ConfigLogic;

/**
 * 配置新增-控制器
 */
class ConfigSave extends Api
{
    public $restMethodList = 'get|put|post';
    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function save()
    {
        $request = $this->selectParam([
            'content',
            'value',
            'key',
            'site_id'=>1
        ]);
        $this->check($request, 'Config.csave');
        $result = ConfigLogic::save($request, $this->userInfo);
        $this->render(200, ['result' => $result]);
    }




}
