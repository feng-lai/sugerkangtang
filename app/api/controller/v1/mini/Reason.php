<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;

/**
 * 取消订单原因控制器
 */
class Reason extends Api
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
            'type'=>1
        ]);
        $result = \app\api\model\Reason::build()
            ->order('order_number desc')
            ->where('status',1)
            ->where('is_deleted',1)
            ->where('site_id',$request['site_id'])
            ->where('type',$request['type'])
            ->column('content');
        $this->render(200, ['result' => $result]);
    }
}
