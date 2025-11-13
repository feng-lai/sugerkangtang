<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;

/**
 * 首页海报控制器
 */
class Poster extends Api
{
    public $restMethodList = 'get';


    public function _initialize()
    {
        parent::_initialize();
        //$this->userInfo = $this->miniValidateToken();
    }

    public function index()
    {
        $request = $this->selectParam([
            'site_id'=>1,
        ]);
        $result = \app\api\model\Poster::build()->where('status',1)->where('site_id',$request['site_id'])->select();
        $this->render(200, ['result' => $result]);
    }
}
